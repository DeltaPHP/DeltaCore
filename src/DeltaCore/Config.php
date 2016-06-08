<?php
/**
 * User: Vasiliy Shvakin (orbisnull) zen4dev@gmail.com
 */

namespace DeltaCore;

use DeltaUtils\ArrayUtils;
use DeltaUtils\Object\Collection;

class Config implements  \ArrayAccess, \IteratorAggregate
{
    const DYN_CONF = "__dynamic__";

    protected $configRaw;

    protected $childConfig = [];

    protected $environment;

    function __construct(array $config, $environment = null)
    {
        $this->set($config);
        $this->setEnvironment($environment);
    }

    /**
     * @return mixed
     */
    public function getEnvironment()
    {
        return $this->environment;
    }

    /**
     * @param mixed $environment
     */
    public function setEnvironment($environment)
    {
        $this->environment = $environment;
    }

    public function set($data, array $path = null)
    {
        $this->childConfig = [];

        if (is_null($path)) {
            $this->configRaw = (array) $data;
            return;
        }
        $this->configRaw = ArrayUtils::set($this->configRaw, $path, $data);
    }

    public function joinLeft($data)
    {
        if ($data instanceof Config) {
            $data = $data->toArray();
        } else {
            $data = (array) $data;
        }
        $this->configRaw = ArrayUtils::mergeRecursive($data, $this->configRaw);
        $this->childConfig = [];
        return $this;
    }

    public function joinRight($data)
    {
        if ($data instanceof Config) {
            $data = $data->toArray();
        } else {
            $data = (array) $data;
        }
        $this->configRaw = ArrayUtils::mergeRecursive($this->configRaw, $data);
        $this->childConfig = [];
        return $this;
    }

    /**
     * @param array|string $path
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
                return is_array($default) && !is_callable($default) ? new Config($default, $this->getEnvironment()) : $default;
            }
            $needConfig = ArrayUtils::get($this->configRaw, $path, $default);
            if (is_array($needConfig)) {
                $firstElement = reset($needConfig);
                if (key($needConfig) === self::DYN_CONF && is_callable($firstElement)) {
                    $needConfig = call_user_func($firstElement, $this->getEnvironment());
                }
            }
            if (is_array($needConfig) && !is_callable($needConfig)) {
                $this->childConfig[$pathKey] = new Config($needConfig, $this->getEnvironment());
            } else {
                $this->childConfig[$pathKey] = $needConfig;
            }
        }
        return $this->childConfig[$pathKey];
    }

    public function getAndCall($path, $callback, array $arguments = null)
    {
        if (ArrayUtils::issetByPath($this->configRaw, $path)) {
            $value = $this->get($path);
            if (null !== $arguments) {
                array_unshift($arguments, $value);
            }
            else {
                $arguments = [$value];
            }
            return call_user_func_array($callback, $arguments);
        }
    }

    public function getOrThrow($path)
    {
        $data = $this->get($path);
        if (null === $data) {
            throw new \Exception("$path not found in config");
        }
        return $data;
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
        $this->configRaw[$offset] = $value;
        if (isset($this->childConfig[$offset])) {
            unset($this->childConfig[$offset]);
        }

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
        if (isset($this->configRaw[$offset])) {
            unset($this->configRaw[$offset]);
        }
        if (isset($this->childConfig[$offset])) {
            unset($this->childConfig[$offset]);
        }
    }

    public function toArray()
    {
        return (array)$this->configRaw;
    }

    public function toCollection()
    {
        return new Collection($this->configRaw);
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->toArray());
    }

}
