<?php
/**
 * User: Vasiliy Shvakin (orbisnull) zen4dev@gmail.com
 */

namespace DeltaCore\View;

use DeltaCore\InterfaceView;

class BlitzView implements InterfaceView1
{
    /**
     * @var \Blitz
     */
    protected $parser;
    protected $vars = [];
    protected $globalVars = [];
    protected $template;
    protected $templateDir;
    protected $templateExt = '.html';
    protected $templateString;

    function __construct($templateDir = null)
    {
        if (!is_null($templateDir)) {
            $this->setTemplateDir($templateDir);
        }
    }

    /**
     * @return \Blitz
     */
    public function getParser()
    {
        if (is_null($this->parser)) {
            $this->parser = new \Blitz();
        }
        return $this->parser;
    }

    /**
     * @param mixed $templateDir
     */
    public function setTemplateDir($templateDir)
    {
        $this->templateDir = $templateDir;
    }

    /**
     * @return mixed
     */
    public function getTemplateDir()
    {
        return $this->templateDir;
    }

    /**
     * @param string $templateExt
     */
    public function setTemplateExt($templateExt)
    {
        $this->templateExt = $templateExt;
    }

    /**
     * @return string
     */
    public function getTemplateExt()
    {
        return $this->templateExt;
    }

    public function setTemplate($name)
    {
        $templateFile = $this->calcTemplatePath($name);
        $this->setTemplateFile($templateFile);
    }

    public function calcTemplatePath($relativeTemplate)
    {
        return $this->getTemplateDir() . '/' . $relativeTemplate . $this->getTemplateExt();
    }

    public function setTemplateFile($path, $isRelativePath = true)
    {
        $this->template = $path;
    }

    public function isTemplateExists($template)
    {
        $templateFile = $this->calcTemplatePath($template);
        return is_readable($templateFile);
    }

    /**
     * @return mixed
     */
    public function getTemplateFile()
    {
        return $this->template;
    }


    protected function loadTemplate($file)
    {
        $
        $this->getParser()->load(file_get_contents($file));
    }

    public function render($template, $params = [])
    {
        if (!is_null($template)) {
            $this->setTemplate($template);
        }

        if (!is_null($vars)) {
            $this->setArray($vars);
        }

        if (!is_null($globalVars)) {
            $this->setGlobalArray($globalVars);
        }

        if (!isset($this->template)) {
            throw new \LogicException('Template not defined');
        }

        $this->loadTemplate($this->getTemplateFile());
        $this->assignVarsToRender();

        return $this->getParser()->parse();
    }

    public function assign($name, $value)
    {
        $this->vars[$name] = $value;
        return $this;
    }

    public function assignGlobal($name, $value)
    {
        $this->globalVars[$name] = $value;
        return $this;
    }

    public function assignArray(array $array)
    {
        $this->vars += $array;
    }

    public function assignGlobalArray(array $array)
    {
        $this->globalVars += $array;
    }

    /**
     * @return array
     */
    public function getGlobalVars()
    {
        return $this->globalVars;
    }

    /**
     * @return array
     */
    public function getVars()
    {
        return $this->vars;
    }

    protected function assignVarsToRender ()
    {
        if (count($this->getVars()) > 0 ) {
            $this->getParser()->set($this->getVars());
        }

        if (count($this->getGlobalVars()) > 0 ) {
            $this->getParser()->set($this->getGlobalVars());
        }
    }

    public function clearVars()
    {
        $this->vars = [];
        $this->globalVars = [];
    }

    public function setArrayTemplates($templateString)
    {
        $this->templateString = $templateString;
    }

    public function display($template = null, $params = [])
    {
        echo $this->render($template, $params);
    }


} 