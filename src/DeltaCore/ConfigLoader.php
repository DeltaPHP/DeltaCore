<?php
/**
 * User: Vasiliy Shvakin (orbisnull) zen4dev@gmail.com
 */

namespace DeltaCore;


use OrbisTools\ArrayUtils;

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
        $file = $this->getConfigDir() . "/{$type}config.php";
        if (file_exists($file)) {
            return (array) include ($file);
        } else {
            return [];
        }
    }

    /**
     * @return Config
     */
    public function getConfig()
    {
        if (is_null($this->configObj)) {
            $defaultConfig = $this->getDefaultConfig();
            $globalConfig = $this->readConfig(self::GLOBAL_CONFIG);
            $localConfig = $this->readConfig(self::GLOBAL_CONFIG);
            $config = ArrayUtils::merge_recursive($defaultConfig, $globalConfig, $localConfig);
            $this->configObj = new Config($config);
        }
        return $this->configObj;
    }


}