<?php
namespace DeltaCore;

use Composer\Autoload\ClassLoader;
use DeltaCore\Exception\AccessDeniedException;
use DeltaRouter\Router;
use dTpl\InterfaceView;
use HttpWarp\Exception\HttpUsableException;
use HttpWarp\Request;
use HttpWarp\Response;
use HttpWarp\Session;

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

        $this['sessions'] = function($c){
            return $c->getSession();
        };
        $this['request'] = function(){
            return $this->getRequest();
        };
        $this['response'] = function(){
            return $this->getResponse();
        };
        $this['router'] = function(){
            return $this->getRouter();
        };
        $this['view'] = function(){
            return $this->getView();
        };

        $this["moduleManager"] = function($c) {
            $modulesList = $c->getConfig("modules", [])->toArray();
            $mm = new ModuleManager($modulesList);
            $mm->setLoader($c->getLoader());
            $mm->setView($c->getView());
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

    public function getConfig($path = null, $default = null)
    {
        if (is_null($this->config)) {
            $this->config = $this->getConfigLoader()->getConfig();
        }
        return $this->config->get($path, $default);
    }

    public function readRouters()
    {
        $routersFile = ROOT_DIR . '/config/routers.php';
        if (!file_exists($routersFile)) {
            return [];
        }
        $routers = include $routersFile;

        if (!is_array($routers)) {
            $routers = [];
        }
        return $routers;
    }

    public function readResources()
    {
        $resourcesFile = ROOT_DIR . '/config/resources.php';
        if (!file_exists($resourcesFile)) {
            return [];
        }
        $resources = include $resourcesFile;

        if (!is_array($resources)) {
            $resources = [];
        }
        return $resources;
    }

    public function setRouters(array $routers)
    {
        foreach($routers as $route) {
            $path = $route[0];
            if (count($route) === 3) {
                $method = $route[1];
                $closure = $route[2];
            } else {
                $method = Router::METHOD_ALL;
                $closure = $route[1];
            }

            $args = null;
            if (is_array($closure)) {
                $args = $closure;
                $closure = [$this, 'action'];
            }

            $this->getRouter()->setUrl($path, $closure, $method, $args);
        }
        return true;
    }

    public function setResources(array $resources)
    {
        foreach($resources as $name=>$value) {
            $this[$name] = $value;
        }
    }

    public function getErrorFunction($errorCode)
    {
        $closure = $this->getConfig(["errors", $errorCode], function() use ($errorCode) {
            $response = new Response();
            $response->setBody('<!DOCTYPE html> <html lang="ru"> <head> <meta charset="utf-8"> <title>Error</title> </head> <body> <header> <h1>Error</h1> </header> </body>');
            $response->setCode($errorCode);
            $response->sendReplay();
        });
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
            $this->action($closure[0], $closure[1]);
        } elseif (is_callable($closure)) {
            call_user_func($closure);
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

        $globalResources =$this->readResources();
        $resources = $mm->getResources();
        $resources = array_merge($resources, $globalResources);
        $this->setResources($resources);
        //resourses
        $mm->load();

        /** @var \Closure[] $initClosures */
        $initClosures = $this->getConfig("init", [])->toArray();
        foreach($initClosures as $initClosure) {
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
            $this->response->setConfig($this->getConfig('response'));
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
            $viewVars = $this->getConfig(['view', 'vars'], [])->toArray();
            $this->view->assignArray($viewVars);
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

    public function action($controller, $action)
    {
        $actionName = lcfirst($action);
        $action = $actionName .'Action';

        $view = $this->getView();

        if (!is_array($controller)) {
            $controllerName = lcfirst($controller);
            $template = $controllerName . '/' . $actionName;
            $controller = '\\Controller\\' . ucfirst($controllerName) . 'Controller';
        } else {
            $module = $controller["module"];
            $controllerId = lcfirst($controller["controller"]);
            $controllerName = "{$module}/{$controllerId}";
            $controller = "\\{$module}\\Controller\\" . ucfirst($controllerId) . 'Controller';
            $template = "{$controllerName}/{$actionName}";
        }

        /** @var AbstractController $controller */
        $controller = new $controller();
        if (!$controller instanceof AbstractController) {
            throw new \ErrorException();
        }
        $controller->setApplication($this);
        $controller->setRequest($this->getRequest());
        $controller->setResponse($this->getResponse());

        if (!$view->exist($template)) {
            if ($actionName === "add" or $actionName === "edit") {
                $template2 = "{$controllerName}/form";
                if ($view->exist($template2)) {
                    $template = $template2;
                }
            }
        }

        $view->setTemplate($template);
        $view->assignArray(['_controller' => $controllerName,
                            '_action'     => $actionName,
                            '_path'       => $controllerName . '/' . $actionName
        ]);
        $controller->setView($view);

        if (!$controller->checkAccess()) {
            throw new AccessDeniedException();
        }

        $controller->init();
        $controller->$action();
        $controller->finalize();

        if ($controller->isAutoRender()) {
            $html = $controller->getView()->render();
            $response = $controller->getResponse();
            $response->setBody($html);
            $response->sendReplay();
        }
    }

    public function isAllow($resource = null, User $user= null)
    {
       if (isset($this["aclManager"])) {
           /** @var \Acl\Model\AclManager $aclManager */
           $aclManager = $this['aclManager'];
           if (!$resource) {
               /** @var Request $request */
               $request = $this['request'];
               $resource = $request->getUriNormal();
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