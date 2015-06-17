<?php
/**
 * User: Vasiliy Shvakin (orbisnull) zen4dev@gmail.com
 */

namespace DeltaCore\Parts;

use DeltaCore\Config;

trait Configurable
{
    /** @var  \DeltaCore\Config */
    protected $config;

    /**
     * @param null $path
     * @param null $default
     * @return Config|null
     */
    public function getConfig($path = null, $default = null)
    {
        if (!$this->config instanceof Config) {
            return is_array($default) ? new Config([]) : $default;;
        }
        if ($path)  {
            return $this->config->get($path, $default);
        }
        return $this->config;
    }

    /**
     * @param \DeltaCore\Config $config
     */
    public function setConfig($config)
    {
        $this->config = $config;
    }
}
