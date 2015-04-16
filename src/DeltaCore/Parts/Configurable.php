<?php
/**
 * User: Vasiliy Shvakin (orbisnull) zen4dev@gmail.com
 */

namespace DeltaCore\Parts;

trait Configurable
{
    /** @var  \DeltaCore\Config */
    protected $config;

    /**
     * @return \DeltaCore\Config
     */
    public function getConfig()
    {
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
