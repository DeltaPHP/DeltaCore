<?php
/**
 * User: Vasiliy Shvakin (orbisnull) zen4dev@gmail.com
 */

namespace DeltaCore;


interface ConfigurableInterface
{
    /**
     * @return Config|null
     */
    public function getConfig();

    /**
     * @param Config $config
     */
    public function setConfig($config);

}
