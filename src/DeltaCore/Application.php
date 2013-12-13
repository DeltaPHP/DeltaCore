<?php
namespace DeltaCore;

use Kindrouter\Router;
use OrbisTools\Request;
use OrbisTools\Response;

class Application
{
    protected $router;
    protected $config;
    protected $view;
    /**
     * @var Request
     */
    protected $request;
    /**
     * @var Response
     */
    protected $response;

    function __construct()
    {
        if (!defined('ROOT_DIR') || !ROOT_DIR) {
            $rootDir = realpath(__DIR__ . '../../../../');
            define('ROOT_DIR', $rootDir);
        }
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

    public function getConfig($path = null, $default = null)
    {
        if (is_null($this->config)) {
            $this->config = new Config();
        }
        return $this->config->getConfig($path, $default);
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

    public function getView()
    {
        if (is_null($this->view)) {
            $viewConfig = $this->getConfig('view');
            $viewAdapter = $this->getConfig(['view', 'adapter'], 'Twig');
            $this->view = ViewFactory::getView($viewAdapter, $viewConfig);
        }
        return $this->view;
    }

    public function action($controller, $action)
    {
        $actionName = lcfirst($action);
        $controllerName = lcfirst($controller);
        $template = $controllerName . '/' . $actionName;
        $controller = ucfirst($controller) . 'Controller';
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

    public function getResponse()
    {
        if (is_null($this->response)) {
            $this->response = new Response();
        }
        return $this->response;
    }

}