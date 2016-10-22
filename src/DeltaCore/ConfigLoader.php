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
    const AUTO_CONFIG = 'auto';
    const LEVEL_APP = "App";
    const LEVEL_PROJECT = "Project";
    const LEVELS = [self::LEVEL_APP, self::LEVEL_PROJECT];

    protected $configDirs;
    protected $configObj;
    protected $defaultConfig = [];

    function __construct(array $configDirs = null)
    {
        if (empty($configDirs)) {
            $configDirs = [
                self::LEVEL_APP => ROOT_DIR . '/App/config',
                self::LEVEL_PROJECT => ROOT_DIR . '/config',
            ];
        }
        $this->setConfigDirs($configDirs);
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
     * @param mixed $configDirs
     */
    public function setConfigDirs($configDirs)
    {
        $this->configDirs = $configDirs;
    }

    /**
     * @return mixed
     */
    public function getConfigDir($level)
    {
        return $this->configDirs[$level];
    }

    public function readConfig($level, $type)
    {
        $file = $this->getConfigDir($level) . "/{$type}.config.php";
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
            $appAutoConfig = $this->readConfig(self::LEVEL_APP, self::AUTO_CONFIG);
            $appGlobalConfig = $this->readConfig(self::LEVEL_APP, self::GLOBAL_CONFIG);
            $appLocalConfig = $this->readConfig(self::LEVEL_APP, self::LOCAL_CONFIG);
            $projectAutoConfig = $this->readConfig(self::LEVEL_PROJECT, self::AUTO_CONFIG);
            $projectGlobalConfig = $this->readConfig(self::LEVEL_PROJECT, self::GLOBAL_CONFIG);
            $projectLocalConfig = $this->readConfig(self::LEVEL_PROJECT, self::LOCAL_CONFIG);

            $config = ArrayUtils::mergeRecursiveDisabled($defaultConfig, $appAutoConfig, $appGlobalConfig, $appLocalConfig, $projectAutoConfig, $projectGlobalConfig, $projectLocalConfig);
            $this->configObj = new Config($config, $environment);
        }
        return $this->configObj;
    }
}
