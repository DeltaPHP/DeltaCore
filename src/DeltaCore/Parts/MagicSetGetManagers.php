<?php
/**
 * User: Vasiliy Shvakin (orbisnull) zen4dev@gmail.com
 */

namespace DeltaCore\Parts;


trait MagicSetGetManagers
{
    /** @var \DeltaDb\Repository[]|Callable[] */
    private $managers = [];

    private function setManager($name, $object)
    {
        $this->managers[$name] = $object;
    }

    private function getManager($name)
    {
        if (!array_key_exists($name, $this->managers)) {
            return null;
        }
        if (is_callable($this->managers[$name])) {
            $this->managers[$name] = call_user_func($this->managers[$name]);
        }
        return $this->managers[$name];
    }

    function __call($name, $arguments)
    {
        $action = substr($name, 0, 3);
        if (!$action) {
            throw new \BadMethodCallException("Method " . $name . " not exist in " . get_class($this));
        }
        if (substr($name, -7, 7) === "Manager") {
            $managerName = substr($name, 3, -7);
            switch ($action) {
                case "set":
                    if (!isset($arguments[0]) || !is_object($arguments[0])) {
                        throw new \InvalidArgumentException("try set empty object in {$managerName} manager");
                    }
                    $managerObject = $arguments[0];
                    $this->setManager($managerName, $managerObject);
                    return $this;
                    break;
                case "get" :
                    return $this->getManager($managerName);
                    break;
                default :
                    throw new \BadMethodCallException("Managers may be only set and get, no method " . $name . " not exist in " . get_class($this));
            }
        }
        throw new \BadMethodCallException("Method " . $name . " not exist in " . get_class($this));
    }


}