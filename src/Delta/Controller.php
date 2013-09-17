<?php

namespace Delta;

use OrbisTools\Request;
use OrbisTools\Response;

class Controller
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
     * @var ViewBlitz
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
     * @param \OrbisTools\Request $request
     */
    public function setRequest($request)
    {
        $this->request = $request;
    }

    /**
     * @return \OrbisTools\Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @param \OrbisTools\Response $response
     */
    public function setResponse($response)
    {
        $this->response = $response;
    }

    /**
     * @return \OrbisTools\Response
     */
    public function getResponse()
    {
        return $this->response;
    }


    public function getConfig($path = null)
    {
        //
    }

    /**
     * @param \Delta\ViewBlitz $view
     */
    public function setView($view)
    {
        $this->view = $view;
    }

    /**
     * @return \Delta\ViewBlitz
     */
    public function getView()
    {
        if (is_null($this->view)) {
            $this->view = new ViewBlitz(PUBLIC_DIR . '/templates');
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

    public function init() {return;}

    public function finalize() {return;}








}