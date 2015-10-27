<?php
/**
 * User: Vasiliy Shvakin (orbisnull) zen4dev@gmail.com
 */

namespace DeltaCore\View;

use Assetic\Factory\AssetFactory;
use DeltaCore\Config;
use dTpl\AbstractView;
use dTpl\InterfaceView;
use DeltaUtils\ArrayUtils;

class TwigView extends AbstractView implements InterfaceView
{
    protected $templateExtension = 'twig';

    protected $formCsrfProvider;
    protected $formValidator;
    protected $formFactory;

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

    protected function getFormCsrfProvider()
    {
        return null;
        if (is_null($this->formCsrfProvider)) {
            $this->formCsrfProvider = new \Symfony\Component\Form\Extension\Csrf\CsrfProvider\DefaultCsrfProvider(hash("md5", $_SERVER["SERVER_NAME"]));
        }
        return $this->formCsrfProvider;
    }

    protected function getFormValidator()
    {
        if (is_null($this->formValidator)) {
            $this->formValidator = \Symfony\Component\Validator\Validation::createValidator();
        }
        return $this->formValidator;

    }

    public function initExtension($extension, \Twig_Environment $render, \Twig_Loader_Filesystem $fileSystemLoader)
    {

        if (0 !== strpos($extension, "\\")) {
            $extension = "\\" . $extension;
        }
        $config = $this->getConfig();

        switch ($extension) {
            case "\\TranslationExtension":
                $config = isset($config["translationExtension"]) ? $config["translationExtension"] : [];
                $lang = isset($config["lang"]) ? $config["lang"] : "ru";
                $locale = isset($config["locale"]) ? $config["locale"] : "ru_RU";
                $translator = new \Symfony\Component\Translation\Translator($locale);
                $translator->addLoader('xlf', new \Symfony\Component\Translation\Loader\XliffFileLoader());
                $vendorFormDir = VENDOR_DIR . '/symfony/form/Symfony/Component/Form';
                $vendorValidatorDir = VENDOR_DIR . '/symfony/validator/Symfony/Component/Validator';
                $translator->addResource('xlf', $vendorFormDir . "/Resources/translations/validators.{$lang}.xlf", $locale, 'validators');
                $translator->addResource('xlf', $vendorValidatorDir . "/Resources/translations/validators.{$lang}.xlf", $locale, 'validators');
                $extension = new \Symfony\Bridge\Twig\Extension\TranslationExtension($translator);
                break;
            case "\\FormExtension":
                $config = isset($config["formExtension"]) ? $config["formExtension"] : [];
                $templates = $config["templates"] ?: "/vendor/symfony/twig-bridge/Resources/views/Form";
                $templates = $this->getRootDir() . "/" . $templates;
                $fileSystemLoader->addPath($templates);
                $formTemplate = $config["formTheme"] ?: "form_div_layout.html.twig";
                $formTemplate = (array) $formTemplate;
                $formEngine = new \Symfony\Bridge\Twig\Form\TwigRendererEngine($formTemplate);
                $formEngine->setEnvironment($render);
                $extension = new \Symfony\Bridge\Twig\Extension\FormExtension(new \Symfony\Bridge\Twig\Form\TwigRenderer($formEngine, $this->getFormCsrfProvider()));
                break;
            case "\\Assetic\\Extension\\Twig\\AsseticExtension":
                $config = isset($config["assetic"]) ? $config["assetic"] : [];
                $factoryConfig = isset($config["factory"]) ? $config["factory"] : [];
                $root = isset($factoryConfig["root"]) ? $factoryConfig["root"] : PUBLIC_DIR . "/assets/";
                $debug = isset($factoryConfig["debug"]) ? $factoryConfig["debug"] : false;
                $factory = new AssetFactory($root, $debug);
                $extension = new \Assetic\Extension\Twig\AsseticExtension($factory);
                break;
            default:
                $extension = new $extension;;
        }
        return $extension;
    }

    /**
     * @return \Twig_Environment
     */
    public function getRender()
    {
        if (is_null($this->render)) {
            $config = $this->getConfig();
            $templateDirs = $this->getTemplateDirs();
            $loaderFs = new \Twig_Loader_Filesystem($templateDirs);
            $templatesArray = $this->getArrayTemplates();
            if (!empty($templateArrays)) {
                $arrayLoader = new \Twig_Loader_Array($templatesArray);
                $loader = new \Twig_Loader_Chain([$loaderFs, $arrayLoader]);
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
            $loader = isset($loader) ? $loader : $loaderFs;
            $this->render = new \Twig_Environment($loader, $options);

            $extensions = isset($config['extensions']) ? $config['extensions']: [];
            if ($extensions instanceof Config) {
                $extensions = $extensions->toArray();
            }
            foreach ($extensions as $extension) {
                $extension = $this->initExtension($extension, $this->render, $loaderFs);
                $this->render->addExtension($extension);
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
        $vars = ArrayUtils::mergeRecursive($vars, $params);
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

    public function getFormFactory()
    {
        // Set up the Form component
        if (is_null($this->formFactory)) {
            $this->formFactory = \Symfony\Component\Form\Forms::createFormFactoryBuilder()
//                ->addExtension(new \Symfony\Component\Form\Extension\Csrf\CsrfExtension($this->getFormCsrfProvider()))
                ->addExtension(new \Symfony\Component\Form\Extension\Validator\ValidatorExtension($this->getFormValidator()))
                ->getFormFactory();
        }
        return $this->formFactory;
    }
}
