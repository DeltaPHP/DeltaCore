<?php
namespace DeltaCore;

use DeltaRouter\Router;
use HttpWarp\Request;
use HttpWarp\Response;
use HttpWarp\Session;

class Application extends \Pimple
{
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
            $this->loadRouters();
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

    public function loadRouters()
    {
        $routersFile = ROOT_DIR . '/config/routers.php';
        if (!file_exists($routersFile)) {
            return false;
        }
        $routers = include $routersFile;

        if (!is_array($routers)) {
            throw new \RuntimeException('Bad routers file');
        }

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

    public function run()
    {
        return $this->getRouter()->run();
    }

    function __invoke()
    {
        return $this->run();
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
            $this->view = ViewFactory::getView($viewAdapter, $viewConfig);
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
        $controllerName = lcfirst($controller);
        $template = $controllerName . '/' . $actionName;
        $controller = '\\Controller\\' . ucfirst($controller) . 'Controller';
        $action = $actionName .'Action';

        /** @var AbstractController $controller */
        $controller = new $controller();
        if (!$controller instanceof AbstractController) {
            throw new \ErrorException();
        }
        $controller->setApplication($this);
        $controller->setRequest($this->getRequest());
        $controller->setResponse($this->getResponse());

        $view = $this->getView();

        $view->setTemplate($template);
        $view->assignArray(['_controller' => $controllerName,
                            '_action'     => $actionName,
                            '_path'       => $controllerName . '/' . $actionName
        ]);
        $controller->setView($view);

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



}