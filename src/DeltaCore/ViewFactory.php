<?php
/**
 * User: Vasiliy Shvakin (orbisnull) zen4dev@gmail.com
 */

namespace DeltaCore;

use DeltaCore\View\TwigView;
use dTpl\InterfaceView;

class ViewFactory
{

    /**
     * @param $adapterName
     * @param Config $config
     * @return InterfaceView
     */
    public static function getView($adapterName, Config $config = null)
    {
        $adapterName = strtolower($adapterName);
        switch($adapterName) {
            case 'twig' :
                $view = new TwigView();
                $view->setConfig($config);
                return $view;
                break;
            default:
                throw new \InvalidArgumentException("View adapter $adapterName not defined");
        }
    }
}
