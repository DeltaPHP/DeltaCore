<?php

namespace DeltaCore;

use HttpWarp\Request;
use HttpWarp\Response;
use dTpl\InterfaceView;

abstract class AbstractController
{
    /**
     * @var Application
     */
    protected $application;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var Response
     */
    protected $response;

    /**
     * @var InterfaceView
     */
    protected $view;

    protected $autoRender = true;

    /**
     * @param $application Application
     */
    public function setApplication($application)
    {
        $this->application = $application;
    }

    public function getApplication()
    {
        return $this->application;
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
        return $this->request;
    }

    /**
     * @param Response $response
     */
    public function setResponse($response)
    {
        $this->response = $response;
    }

    /**
     * @return Response
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @param null $path
     * @param null $default
     * @return Config|mixed
     */
    public function getConfig($path = null, $default = null)
    {
        return $this->getApplication()->getConfig($path, $default);
    }

    /**
     * @param InterfaceView $view
     */
    public function setView($view)
    {
        $this->view = $view;
    }

    /**
     * @return InterfaceView
     */
    public function getView()
    {
        if (is_null($this->view)) {
            $viewAdapter = $this->getConfig(['view','adapter'], 'twig');
            $this->view = ViewFactory::getView($viewAdapter, $this->getConfig('view'));

        }
        return $this->view;
    }

    public function autoRenderOff()
    {
        $this->autoRender = false;
    }

    public function autoRenderOn()
    {
        $this->autoRender = true;
    }

    public function isAutoRender()
    {
        return $this->autoRender;
    }

    public function getControllerName()
    {
        $class = get_class($this);
        $class = explode("\\", $class);
        $class = $class[count($class)-1];
        $class = substr($class, 0, -10);
        $class = lcfirst($class);
        return $class;
    }

    public function getModuleName()
    {
        $class = get_class($this);
        $class = explode("\\", $class);
        $module = $class[0];
        return $module === "Controller" ? null : $module;
    }

    public function setViewTemplate($template)
    {
        if(strpos($template, "/") === false) {
            $controller = $this->getControllerName();
            $module = $this->getModuleName();
            $template = $module ? "{$module}/{$controller}/{$template}" : "{$controller}/{$template}";
        }
        $this->getView()->setTemplate($template);
    }

    public function getPage()
    {
        return (integer) $this->getRequest()->getParam("p", 1);
    }

    public function getPageInfo($count, $perPage = 10)
    {
        $page = $this->getPage();
        $countPages = ceil($count / $perPage);
        $page = ($page < 1) ? 1 : ($page > $countPages) ? $countPages : $page;
        $offset = ($page - 1) * $perPage;
        $pages = [];
        for ($i = 1; $i <= $countPages; $i++) {
            $pages[] = ["id" => $i, "active" => $i === $page];
        }
        $uri = $_SERVER['REQUEST_URI'];
        if (strpos($uri, '?')) {
            $path = substr($uri, 0, strpos($uri, '?'));
        } else {
            $path = $uri;
        }
        return [
            "path"          => $path,
            "page"          => $page,
            "countPages"    => $countPages,
            "offsetForPage" => ($offset >= 0) ? $offset : 0,
            "perPage"       => $perPage,
            "pages"         => $pages,
            "showFirstPage" => $page != 1,
            "showLastPage"  => $page != $countPages,
        ];
    }

    public function redirect($url)
    {
        $this->getResponse()->redirect($url);
    }

    public function checkAccess()
    {
        return $this->getApplication()->isAllow();
    }

    public function init() {return;}

    public function finalize() {return;}

}
