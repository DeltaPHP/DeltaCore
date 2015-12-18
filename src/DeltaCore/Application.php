<?php
namespace DeltaCore;

use Composer\Autoload\ClassLoader;
use DeltaCore\Exception\AccessDeniedException;
use DeltaCore\View\TwigView;
use DeltaRouter\Route;
use DeltaRouter\Router;
use DeltaUtils\FileSystem;
use dTpl\InterfaceView;
use HttpWarp\Exception\HttpUsableException;
use HttpWarp\Request;
use HttpWarp\Response;
use HttpWarp\Session;
use User\Model\User;

class Application extends DI
{
    /**
     * @var ClassLoader
     */
    protected $loader;

    /**
     * @var Router
     */
    protected $router;
    /**
     * @var ConfigLoader
     */
    protected $configLoader;
    /**
     * @var Config
     */
    protected $config;
    /**
     * @var InterfaceView
     */
    protected $view;

    /**
     * @var Request
     */
    protected $request;
    /**
     * @var Response
     */
    protected $response;

    /**
     * @var Session
     */
    protected $session;

    function __construct()
    {
        parent::__construct();

        if (!defined('ROOT_DIR') || !ROOT_DIR) {
            $rootDir = realpath(__DIR__ . '../../../../../');
            define('ROOT_DIR', $rootDir);
        }

        $this['sessions'] = function () {
            return $this->getSession();
        };
        $this['request'] = function () {
            return $this->getRequest();
        };
        $this['response'] = function () {
            return $this->getResponse();
        };
        $this['router'] = function () {
            return $this->getRouter();
        };
        $this['view'] = function () {
            return $this->getView();
        };

        $this["config"] = function () {
            return $this->getConfig();
        };

        $this["moduleManager"] = function () {
            $modulesList = $this->getConfig("modules", [])->toArray();
            $mm = new ModuleManager($modulesList);
            $mm->setLoader($this->getLoader());

            return $mm;
        };
    }

    /**
     * @param \Composer\Autoload\ClassLoader $loader
     */
    public function setLoader($loader)
    {
        $this->loader = $loader;
    }

    /**
     * @return \Composer\Autoload\ClassLoader
     */
    public function getLoader()
    {
        return $this->loader;
    }

    /**
     * @return ModuleManager
     */
    public function getModuleManager()
    {
        return $this["moduleManager"];
    }

    /**
     * @param Request $request
     */
    public function setRequest($request)
    {
        $this->request = $request;
    }

    /**
     * @return Request
     */
    public function getRequest()
    {
        if (is_null($this->request)) {
            $this->request = new Request();
        }

        return $this->request;
    }

    public function getRouter()
    {
        if (is_null($this->router)) {
            $this->router = new Router();
        }

        return $this->router;
    }

    /**
     * @param mixed $configLoader
     */
    public function setConfigLoader($configLoader)
    {
        $this->configLoader = $configLoader;
    }

    public function getConfigLoader()
    {
        if (is_null($this->configLoader)) {
            $this->configLoader = new ConfigLoader();
        }

        return $this->configLoader;
    }

    /**
     * @param null $path
     * @param null $default
     * @return Config
     */
    public function getConfig($path = null, $default = null)
    {
        if (is_null($this->config)) {
            $this->config = $this->getConfigLoader()->getConfig($this);
        }

        return $this->config->get($path, $default);
    }

    public function readRouters()
    {
        $appRoutersPath = $this->getConfigLoader()->getConfigDir(ConfigLoader::LEVEL_APP) . "/routers.php";
        $projectRoutersPath = $this->getConfigLoader()->getConfigDir(ConfigLoader::LEVEL_PROJECT) . "/routers.php";
        $appRouters = FileSystem::getPhpConfig($appRoutersPath);
        $projectRouters = FileSystem::getPhpConfig($projectRoutersPath);
        $routers = array_merge($appRouters, $projectRouters);
        return $routers;
    }

    public function readResources()
    {
        $appResourcesPath = $this->getConfigLoader()->getConfigDir(ConfigLoader::LEVEL_APP) . "/resources.php";
        $projectResourcesPath = $this->getConfigLoader()->getConfigDir(ConfigLoader::LEVEL_PROJECT) . "/resources.php";
        $appResources = FileSystem::getPhpConfig($appResourcesPath);
        $projectResources = FileSystem::getPhpConfig($projectResourcesPath);
        $resources = array_merge($appResources, $projectResources);
        return $resources;
    }

    public function setRoute($route, $name = null)
    {
        if (Route::isShort($route)) {
            $route = Route::shortNormalize($route);
        }
        if (is_array($route["action"])) {
            $route["action"] = array_values($route["action"]);
            $route["args"] = isset($route["args"]) ? array_merge($route["action"], [$route["args"]]) : $route["action"];
            $route["action"] = [$this, 'action'];
        }
        $this->getRouter()->setRoute($route, $name);
    }

    public function setRouters(array $routers)
    {
        foreach ($routers as $name => $route) {
            $this->setRoute($route, $name);
        }

        return true;
    }

    public function setResources(array $resources)
    {
        foreach ($resources as $name => $value) {
            $this[$name] = $value;
        }
    }

    public function getErrorFunction($errorCode)
    {
        $closure = $this->getConfig(["errors", $errorCode]);
        if ($closure instanceof Config) {
            $closure = $closure->toArray();
        }

        return $closure;
    }

    public function catchRunException(\Exception $e)
    {
        $errorCode = $e->getCode();
        $closure = $this->getErrorFunction($errorCode);
        if (is_array($closure)) {
            return $this->action($closure[0], $closure[1]);
        } elseif (is_callable($closure)) {
            return call_user_func($closure);
        } elseif ($errorCode === 404) {
            return $this->getResponse()->error404();
        } else {
            throw $e;
        }
    }

    public function run()
    {
        $mm = $this->getModuleManager();

        $globalRouters = $this->readRouters();
        $modulesRouters = $mm->getRouters();
        $routers = array_merge($modulesRouters, $globalRouters);
        $this->setRouters($routers);

        $modulesConfig = $mm->getConfig();
        $this->getConfigLoader()->joinConfigLeft($modulesConfig);

        $globalResources = $this->readResources();
        $resources = $mm->getResources();
        $resources = array_merge($resources, $globalResources);
        $this->setResources($resources);
        //resourses
        $mm->load();

        /** @var \Closure[] $initClosures */
        $initClosures = $this->getConfig("init", [])->toArray();
        foreach ($initClosures as $initClosure) {
            if (is_callable($initClosure)) {
                call_user_func($initClosure, $this);
            }
        }

        try {
            $this->getRouter()->run();
        } catch (HttpUsableException $e) {
            $this->catchRunException($e);
        }

        return;
    }

    public function getResponse()
    {
        if (is_null($this->response)) {
            $this->response = new Response();
            $this->response->setDefaults($this->getConfig('response', [])->toArray());
        }

        return $this->response;
    }

    public function getView()
    {
        if (is_null($this->view)) {
            $viewConfig = $this->getConfig('view');
            $viewAdapter = $this->getConfig(['view', 'adapter'], 'Twig');
            /** @var InterfaceView view */
            $this->view = ViewFactory::getView($viewAdapter, $viewConfig);
            //set templates dir
            if ($this->view instanceof TwigView) {
                $mm = $this->getModuleManager();
                $modules = $mm->getModulesList();
                foreach ($modules as $moduleName) {
                    $templatesPath = $mm->getModulePath($moduleName) . "/templates";
                    if (file_exists($templatesPath . "/{$moduleName}")) {
                        $this->getView()->addTemplateDir($templatesPath);
                    }
                }
            }
            $viewVars = $this->getConfig(['view', 'vars']);
            if ($viewVars instanceof Config) {
                $viewVars = $viewVars->toArray();
            }
            if (!empty($viewVars) && is_array($viewVars)) {
                foreach ($viewVars as $name => $value) {
                    if (is_callable($value)) {
                        $value = call_user_func($value, $this);
                    }
                    $this->view->assign($name, $value);
                }
            }
        }

        return $this->view;
    }

    /**
     * @param \HttpWarp\Session $session
     */
    public function setSession(Session $session)
    {
        $this->session = $session;
    }

    /**
     * @return \HttpWarp\Session
     */
    public function getSession()
    {
        if (is_null($this->session)) {
            $this->session = new Session();
        }

        return $this->session;
    }

    public function action($controller, $action, ...$arguments)
    {
        $actionName = lcfirst($action);
        $action = $actionName . 'Action';

        $view = $this->getView();
        $httpCachePath = null;

        if (!is_array($controller)) {
            $controllerName = lcfirst($controller);
            $template = $controllerName . '/' . $actionName;
            $controller = '\\Controller\\' . ucfirst($controllerName) . 'Controller';
            $httpCachePath = [$controllerName, $actionName];
        } else {
            $module = ucfirst($controller["module"]);
            $controllerId = lcfirst($controller["controller"]);
            $controllerName = "{$module}/{$controllerId}";
            $controller = "\\{$module}\\Controller\\" . ucfirst($controllerId) . 'Controller';
            $template = "{$controllerName}/{$actionName}";
            $httpCachePath = [$module, $controllerId, $actionName];
        }

        /** @var AbstractController $controller */
        if (!$this->getLoader()->findFile($controller)) {
            $controller = "\\App" . $controller;
        }
        $controller = new $controller();
        if (!$controller instanceof AbstractController) {
            throw new \ErrorException();
        }
        $controller->setApplication($this);
        $controller->setRequest($this->getRequest());
        $response = $this->getResponse();

        if (!empty($httpCachePath)) {
            array_unshift($httpCachePath, "HttpCache");
            $httpCacheParams = $this->getConfig($httpCachePath, [])->toArray();
            $response->setDefaults($httpCacheParams);
        }
        $controller->setResponse($response);

        if (!$view->exist($template)) {
            if ($actionName === "add" || $actionName === "edit") {
                $template2 = "{$controllerName}/form";
                if ($view->exist($template2)) {
                    $template = $template2;
                }
            }
        }
        $view->setTemplate($template);
        $view->assignArray([
            '_controller' => $controllerName,
            '_action' => $actionName,
            '_path' => $controllerName . '/' . $actionName
        ]);
        $controller->setView($view);

        if (!$controller->checkAccess()) {
            throw new AccessDeniedException();
        }

        $controller->init();
        //prepare arguments (merge arrays from args aon route params in one)
        if (!empty($arguments)) {
            $arguments = array_merge(...$arguments);
        } else {
            $arguments = [];
        }
        $result = $controller->$action($arguments);
        $controller->finalize();

        if (is_array($result)) {
            $controller->getView()->assignArray($result);
        }

        if ($controller->isAutoRender()) {
            $html = $controller->getView()->render();
            $response = $controller->getResponse();
            $response->setBody($html);
            $response->sendReplay();
        }
    }

    public function isAllow($resource = null, User $user = null)
    {
        if (isset($this["aclManager"])) {
            /** @var \Acl\Model\AclManager $aclManager */
            $aclManager = $this['aclManager'];
            if (!$resource) {
                /** @var Request $request */
                $request = $this['request'];
                $resource = (string)$request->getUrl()->getPath();
            }
            if (!$user) {
                $user = $aclManager->getUserManager()->getCurrentUser();
            }

            return $aclManager->isAllow($resource, $user);
        }

        return true;
    }

    /**
     * @return User|null
     */
    public function getCurrentUser()
    {
        if (!isset($this['userManager'])) {
            return null;
        }
        /** @var \User\Model\UserManager $userManager */
        $userManager = $this['userManager'];

        return $userManager->getCurrentUser();
    }
}
