<?php
/**
 * User: Vasiliy Shvakin (orbisnull) zen4dev@gmail.com
 */

namespace DeltaCore;


use DeltaUtils\ArrayUtils;

class ConfigLoader
{
    const LOCAL_CONFIG = 'local';
    const GLOBAL_CONFIG = 'global';

    protected $configDir;
    protected $configObj;
    protected $defaultConfig = [];

    function __construct($configDir = null)
    {
        $configDir = $configDir ?: ROOT_DIR . '/config';
        $this->setConfigDir($configDir);
    }

    public function setDefaultConfig(array $config)
    {
        $this->defaultConfig = $config;

    }

    public function getDefaultConfig()
    {
        return $this->defaultConfig;
    }

    /**
     * @param mixed $configDir
     */
    public function setConfigDir($configDir)
    {
        $this->configDir = $configDir;
    }

    /**
     * @return mixed
     */
    public function getConfigDir()
    {
        return $this->configDir;
    }

    public function readConfig($type)
    {
        $file = $this->getConfigDir() . "/{$type}.config.php";
        if (file_exists($file)) {
            return (array) include ($file);
        } else {
            return [];
        }
    }

    public function joinConfigLeft(array $config)
    {
        $confObj = $this->getConfig();
        $confObj->joinLeft($config);
    }

    public function joinConfigRight(array $config)
    {
        $confObj = $this->getConfig();
        $confObj->joinRight($config);
    }

    public function getConfig($environment = null)
    {
        if (is_null($this->configObj)) {
            $defaultConfig = $this->getDefaultConfig();
            $globalConfig = $this->readConfig(self::GLOBAL_CONFIG);
            $localConfig = $this->readConfig(self::LOCAL_CONFIG);
            $config = ArrayUtils::mergeRecursive($defaultConfig, $globalConfig, $localConfig);
            $this->configObj = new Config($config, $environment);
        }
        return $this->configObj;
    }
}
