<?php
/**
 * User: Vasiliy Shvakin (orbisnull) zen4dev@gmail.com
 */

namespace DeltaCore;


use OrbisTools\ArrayUtils;

class Config
{
    const LOCAL_CONFIG = 'local';
    const GLOBAL_CONFIG = 'global';

    protected $configDir;
    protected $config;
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

    public function getConfig($path = null, $default = null)
    {
        if (is_null($this->config)) {
            $defaultConfig = $this->getDefaultConfig();
            $globalConfig = $this->readConfig(self::GLOBAL_CONFIG);
            $localConfig = $this->readConfig(self::GLOBAL_CONFIG);
            $this->config = ArrayUtils::merge_recursive($defaultConfig, $globalConfig, $localConfig);
        }
        if (!is_null($path)) {
            return ArrayUtils::getByPath($this->config, $path, $default);
        }
        return $this->config;
    }
}