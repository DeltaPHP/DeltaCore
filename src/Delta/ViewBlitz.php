<?php

namespace Delta;

class ViewBlitz
{
    /**
     * @var BlitzTemplate
     */
    protected $parser;

    protected $vars = [];

    protected $globalVars = [];

    protected $template;

    protected $templateDir;

    protected $templateExt = '.html';

    function __construct($templateDir = null)
    {
        if (!is_null($templateDir)) {
            $this->setTemplateDir($templateDir);
        }
    }


    /**
     * @return BlitzTemplate
     */
    public function getParser()
    {
        if (is_null($this->parser)) {
            $this->parser = new BlitzTemplate();
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


    /**
     * @param mixed $template
     */
    public function setTemplate($template)
    {
        $templateFile = $this->calcTemplatePath($template);
        $this->setTemplateFile($templateFile);
    }

    public function calcTemplatePath($relativeTemplate)
    {
        return $this->getTemplateDir() . '/' . $relativeTemplate . $this->getTemplateExt();
    }

    public function setTemplateFile($file, $relativePath = true)
    {
        $this->template = $file;
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
        $this->getParser()->load(file_get_contents($file));
    }

    public function parse($template = null, array $vars = null, array $globalVars = null)
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

    public function set($name, $value)
    {
        $this->vars[$name] = $value;
        return $this;
    }

    public function setGlobal($name, $value)
    {
        $this->globalVars[$name] = $value;
        return $this;
    }

    public function setArray(array $array)
    {
        $this->vars += $array;
    }

    public function setGlobalArray(array $array)
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
}