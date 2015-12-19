<?php
/**
 * User: Vasiliy Shvakin (orbisnull) zen4dev@gmail.com
 */

namespace DeltaCore;


use DeltaCore\Prototype\ConfigInterface;

interface ConfigurableInterface extends ConfigInterface
{
    /**
     * @param Config $config
     */
    public function setConfig($config);

}
