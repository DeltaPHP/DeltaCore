<?php
/**
 * User: Vasiliy Shvakin (orbisnull) zen4dev@gmail.com
 */

namespace DeltaCore;


use Composer\Autoload\ClassLoader;
use DeltaUtils\ArrayUtils;
use DeltaUtils\Parts\InnerCache;
use dTpl\AbstractView;

class ModuleManager
{
    use InnerCache;

    /**
     * @var array
     */
    protected $modulesList;

    /**
     * @var ClassLoader
     */
    protected $loader;

    function __construct(array $modules)
    {
        $this->setModulesList($modules);
    }

    /**
     * @param array $modulesList
     */
    public function setModulesList(array $modulesList)
    {
        $this->modulesList = $modulesList;
    }

    /**
     * @return array
     */
    public function getModulesList()
    {
        return $this->modulesList;
    }

    public function addModule($name)
    {
        $this->modulesList[] = $name;
    }

    /**
     * @param \Composer\Autoload\ClassLoader $loader
     */
    public function setLoader($loader)
    {
        $this->loader = $loader;
    }

    /**
     * @return \Composer\Autoload\ClassLoader
     */
    public function getLoader()
    {
        return $this->loader;
    }

    public function load()
    {
        $modules = $this->getModulesList();
        foreach($modules as $moduleName) {
            $class = "\\{$moduleName}\\Module";
            if(is_callable([$class, "init"])) {
                call_user_func([$class, "init"]);
            }
        }
    }

    public function getModulePath($moduleName)
    {
        $cacheKey = "ModulePath|{$moduleName}";
        $path = $this->getInnerCache($cacheKey);
        if ($path) {
            return $path;
        }
        $class = "\\{$moduleName}\\Module";
        $loader = $this->getLoader();
        $file = $loader->findFile($class);
        $path = null;
        if (!$file) {
            $moduleDir = ROOT_DIR . "/modules/{$moduleName}";
            if (!file_exists($moduleDir)) {
                throw new \Exception("module $moduleName not found");
            }
            $path = realpath($moduleDir);
            $loader->add($moduleName, ROOT_DIR . "/modules");
            $loader->addClassMap(["{$moduleName}\\Module" => $path . "/Module.php"]);
        } else {
            $path = dirname($file);
        }
        $this->setInnerCache($cacheKey, $path);
        if (!$path) {
            return false;
        }
        return $path;
    }

    public function getRouters()
    {
        $modules = $this->getModulesList();
        $routers = [];
        foreach ($modules as $module) {
            $path = $this->getModulePath($module);
            if (!$path) {
                throw new \Exception("Path for module $module not found");
            }
            $resourcesFile = $path . "/config/routers.php";
            if (!file_exists($resourcesFile)) {
                continue;
            }
            $moduleRoutes = include $resourcesFile;
            foreach ($moduleRoutes as $key => $route) {
                if (is_array($route[1])) {
                    $moduleRoutes[$key][1] = [
                        [
                            "module"     => $module,
                            "controller" => $route[1][0]
                        ],
                        "action" => $route[1][1],
                    ];
                }
            }
            $routers = array_merge($routers, $moduleRoutes);
        }
        return $routers;
    }

    public function getConfig()
    {
        return $this->getListArrayConfigs("config", true);
    }

    public function getResources()
    {
        return $this->getListArrayConfigs("resources");
    }

    public function getListArrayConfigs($fileConfigName, $recursiveMerge = false)
    {
        $modules = $this->getModulesList();
        $configs = [];
        foreach ($modules as $module) {
            $path = $this->getModulePath($module);
            if (!$path) {
                throw new \Exception("Path for module $module not found");
            }
            $resourcesFile = $path . "/config/{$fileConfigName}.php";
            if (!file_exists($resourcesFile)) {
                continue;
            }
            $moduleResources = include $resourcesFile;
            if ($recursiveMerge) {
                $configs = ArrayUtils::mergeRecursive($configs, $moduleResources);
            } else {
                $configs = array_merge($configs, $moduleResources);
            }
        }
        return $configs;
    }

}