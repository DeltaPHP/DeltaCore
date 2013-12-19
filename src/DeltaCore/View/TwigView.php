<?php
/**
 * User: Vasiliy Shvakin (orbisnull) zen4dev@gmail.com
 */

namespace DeltaCore\View;

use DeltaCore\AbstractView;
use DeltaCore\InterfaceView;
use OrbisTools\ArrayUtils;

class TwigView extends AbstractView implements InterfaceView
{
    const TPL_EXT = 'twig';

    protected $render;
    protected $config;

    protected $vars = [];
    protected $globalVars = [];
    protected $template;
    protected $templateExtension = self::TPL_EXT;
    protected $arrayTemplates;
    protected $templateDirs = [];

    /**
     * @param mixed $config
     */
    public function setConfig($config)
    {
        $this->config = $config;
    }

    /**
     * @return mixed
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @param string $templateExtension
     */
    public function setTemplateExtension($templateExtension)
    {
        $this->templateExtension = $templateExtension;
    }

    /**
     * @return string
     */
    public function getTemplateExtension()
    {
        return $this->templateExtension;
    }

    public function setTemplate($name)
    {
        $this->template = $name;
    }

    /**
     * @return mixed
     */
    public function getTemplate()
    {
        return $this->template;
    }

    public function setArrayTemplates($templatesString, $templateName = self::DEFAULT_TEMPLATE)
    {
        $this->arrayTemplates[$templateName] = $templatesString;
    }

    /**
     * @return array
     */
    public function getArrayTemplates()
    {
        return $this->arrayTemplates;
    }

    /**
     * @param array $templateDirs
     */
    public function setTemplateDirs(array $templateDirs)
    {
        $this->templateDirs = $templateDirs;
    }

    public function addTemplateDirs($directory)
    {
        $this->templateDirs[] = $directory;
    }

    /**
     * @return array
     */
    public function getTemplateDirs()
    {
        $dirs = (array) ArrayUtils::getByPath($this->getConfig(), 'templateDirs', 'public/templates');
        $realDirs = [];
        foreach ($dirs as $dir) {
            if(strpos($dir, '/') === 0) {
                $realDirs[] = $dir;
                continue;
            }
            $dir = realpath(ROOT_DIR . '/' . $dir);
            if ($dir && is_dir($dir)) {
                $realDirs[] = $dir;
            }
        }
        return $realDirs;
    }

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
            $templateDirs = $this->getTemplateDirs();
            $loader = new \Twig_Loader_Filesystem($templateDirs);
            $templatesArray = $this->getArrayTemplates();
            if (!empty($templateArrays)) {
                $arrayLoader = new \Twig_Loader_Array($templatesArray);
                $loader = new \Twig_Loader_Chain([$loader, $arrayLoader]);
            }
            $this->render = new \Twig_Environment($loader);
        }
        return $this->render;
    }

    public function assign($name, $value)
    {
        $this->vars[$name] = $value;
    }

    public function assignArray(array $array)
    {
        foreach ($array as $key => $value) {
            $this->assign($key, $value);
        }
    }

    public function getAssignedVars()
    {
        return $this->vars;
    }

    public function addGlobalVar($name, $value)
    {
        $this->globalVars[$name] = $value;
    }

    /**
     * @return array
     */
    public function getGlobalVars()
    {
        return $this->globalVars;
    }


    public function render($templateName = self::DEFAULT_TEMPLATE, $params = [])
    {
        $vars = $this->getAssignedVars();
        $vars = ArrayUtils::merge_recursive($vars, $params);
        $globalVars = $this->getGlobalVars();
        $render = $this->getRender();
        foreach ($globalVars as $name=>$value) {
            $render->addGlobal($name, $value);
        }
        if ($templateName === self::DEFAULT_TEMPLATE || $templateName = null) {
            $mainTemplate = $this->getTemplate();
            $templateName = $mainTemplate ?: ($templateName ?: self::DEFAULT_TEMPLATE);
        }
        $templateName = $templateName . '.' . $this->getTemplateExtension();
        $output = $render->render($templateName, $vars);
        return $output;
    }
}