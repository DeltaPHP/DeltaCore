<?php
/**
 * User: Vasiliy Shvakin (orbisnull) zen4dev@gmail.com
 */

namespace DeltaCore;

use DeltaUtils\ArrayUtils;

class Config implements  \ArrayAccess
{
    protected $configRaw;

    protected $childConfig = [];

    function __construct(array $config)
    {
        $this->set($config);
    }

    public function set($data, array $path = null)
    {
        $this->childConfig = [];

        if (is_null($path)) {
            $this->configRaw = (array) $data;
            return;
        }
        $this->configRaw = ArrayUtils::setByPath($this->configRaw, $path, $data);
    }

    public function joinLeft(array $data)
    {
        $this->configRaw = ArrayUtils::mergeRecursive($data, $this->configRaw);
        $this->childConfig = [];
    }

    public function joinRight(array $data)
    {
        $this->configRaw = ArrayUtils::mergeRecursive($this->configRaw, $data);
        $this->childConfig = [];
    }

    /**
     * @param null $path
     * @param null $default
     * @return $this|null
     */
    public function get($path = null, $default = null)
    {
        if (is_null($path)) {
            return $this;
        }
        $pathKey = implode('|', (array) $path);
        if (!isset($this->childConfig[$pathKey])) {
            if (!ArrayUtils::issetByPath($this->configRaw, $path)) {
                return $default;
            }
            $needConfig = ArrayUtils::getByPath($this->configRaw, $path, $default);
            if (is_array($needConfig)) {
                $this->childConfig[$pathKey] = new Config($needConfig);
            } else {
                $this->childConfig[$pathKey] = $needConfig;
            }
        }
        return $this->childConfig[$pathKey];
    }

    public function getOneIs(array $paths, $default = null)
    {
        foreach ($paths as $path) {
            $data = $this->get($path);
            if ($data) {
                return $data;
            }
        }
        return $default;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Whether a offset exists
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     * @param mixed $offset <p>
     * An offset to check for.
     * </p>
     * @return boolean true on success or false on failure.
     * </p>
     * <p>
     * The return value will be casted to boolean if non-boolean was returned.
     */
    public function offsetExists($offset)
    {
        return isset($this->configRaw[$offset]);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to retrieve
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset <p>
     * The offset to retrieve.
     * </p>
     * @return mixed Can return all value types.
     */
    public function offsetGet($offset)
    {
        return ($this->offsetExists($offset)) ? $this->get($offset) : null;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to set
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     * @param mixed $offset <p>
     * The offset to assign the value to.
     * </p>
     * @param mixed $value <p>
     * The value to set.
     * </p>
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        throw new \LogicException('Config can`t change on runtime');
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to unset
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset <p>
     * The offset to unset.
     * </p>
     * @return void
     */
    public function offsetUnset($offset)
    {
        throw new \LogicException('Config can`t change on runtime');
    }

    public function toArray()
    {
        return (array)$this->configRaw;
    }
}
