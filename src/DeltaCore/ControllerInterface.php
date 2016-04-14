<?php


namespace DeltaCore;


interface ControllerInterface
{
    /**
     * @param $application Application
     */
    public function setApplication($application);

    public function getApplication();

    /**
     * @param Request $request
     */
    public function setRequest($request);

    /**
     * @return Request
     */
    public function getRequest();

    /**
     * @param Response $response
     */
    public function setResponse($response);

    /**
     * @return Response
     */
    public function getResponse();

    /**
     * @param null $path
     * @param null $default
     * @return Config|mixed
     */
    public function getConfig($path = null, $default = null);

    /**
     * @param ViewInterface $view
     */
    public function setView($view);
    /**
     * @return ViewInterface
     */
    public function getView();

    public function autoRenderOff();

    public function autoRenderOn();

    public function isAutoRender();

    public function getControllerName();

    public function getModuleName();

    public function setViewTemplate($template);

    public function getPage();

    public function getPageInfo($count, $perPage = 10);

    public function redirect($url);

    public function checkAccess();

    public function init();

    public function finalize();

    public function getRouteUrl($routeId, array $params = []);
}
