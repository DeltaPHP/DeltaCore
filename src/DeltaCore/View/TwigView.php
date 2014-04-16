<?php
/**
 * User: Vasiliy Shvakin (orbisnull) zen4dev@gmail.com
 */

namespace DeltaCore\View;

use DeltaCore\Config;
use dTpl\AbstractView;
use dTpl\InterfaceView;
use DeltaUtils\ArrayUtils;

class TwigView extends AbstractView implements InterfaceView
{
    protected $templateExtension = 'twig';

    public function reset()
    {
        unset($this->render);
        $this->$vars = [];
        $this->$globalVars = [];
        unset($this->template);
        $this->$templateExtension = self::TPL_EXT;
        $this->arrayTemplates = [];
        $this->templateDirs = [];
    }

    /**
     * @return \Twig_Environment
     */
    public function getRender()
    {
        if (is_null($this->render)) {
            $config = $this->getConfig();
            $templateDirs = $this->getTemplateDirs();
            $loader = new \Twig_Loader_Filesystem($templateDirs);
            $templatesArray = $this->getArrayTemplates();
            if (!empty($templateArrays)) {
                $arrayLoader = new \Twig_Loader_Array($templatesArray);
                $loader = new \Twig_Loader_Chain([$loader, $arrayLoader]);
            }
            $options = isset($config['options']) ? $config['options']: [];
            if ($options instanceof Config) {
                $options = $options->toArray();
            }
            if (isset($options['cache']) && $options['cache']) {
                $cache = realpath($this->getRootDir() . '/' . $options['cache']);
                if ($cache) {
                    $options['cache'] = $cache;
                } else {
                    unset($options['cache']);
                }
            }
            $this->render = new \Twig_Environment($loader, $options);

            $extensions = isset($config['extensions']) ? $config['extensions']: [];
            if ($extensions instanceof Config) {
                $extensions = $extensions->toArray();
            }
            foreach ($extensions as $extension) {
                $this->render->addExtension(new $extension);
            }
            $filters = isset($config['filters']) ? $config['filters']: [];
            if ($filters instanceof Config) {
                $filters = $filters->toArray();
            }
            foreach ($filters as $name=>$filter) {
                $callable = $filter[0];
                $params = isset($filter[1]) ? $filter[1] : [];
                $this->render->addFilter(new \Twig_SimpleFilter($name, $callable, $params));
            }
        }
        return $this->render;
    }

    public function render($params = [], $templateName = null)
    {
        if (!is_null($templateName)) {
            $this->setTemplate($templateName);
        }
        $vars = $this->getAssignedVars();
        $vars = ArrayUtils::merge_recursive($vars, $params);
        $globalVars = $this->getGlobalVars();
        $render = $this->getRender();
        foreach ($globalVars as $name=>$value) {
            $render->addGlobal($name, $value);
        }
        /** @var \Twig_Environment $template */
        $template = $this->getTemplate();
        $output = $render->render($template, $vars);
        return $output;
    }

    public function exist($template)
    {
        $template = $template . "." . $this->getTemplateExtension();
        $loader = $this->getRender()->getLoader();
        $result = $loader->exists($template);
        return $result;
    }
}