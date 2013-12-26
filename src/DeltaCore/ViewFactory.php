<?php
/**
 * User: Vasiliy Shvakin (orbisnull) zen4dev@gmail.com
 */

namespace DeltaCore;

use DeltaCore\View\TwigView;

class ViewFactory
{
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