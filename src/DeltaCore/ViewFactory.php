<?php
/**
 * User: Vasiliy Shvakin (orbisnull) zen4dev@gmail.com
 */

namespace DeltaCore;

use DeltaCore\View\TwigView;
use OrbisTools\ArrayUtils;

class ViewFactory
{
    public static function getView($adapterName, array $config)
    {
        $adapterName = strtolower($adapterName);
        switch($adapterName) {
            case 'Twig' :
                $view = new TwigView();
                $view->setConfig($config);
                return $view;
        }
    }
} 