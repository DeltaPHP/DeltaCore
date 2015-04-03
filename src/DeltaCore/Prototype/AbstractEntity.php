<?php
/**
 * User: Vasiliy Shvakin (orbisnull) zen4dev@gmail.com
 */

namespace DeltaCore\Prototype;


abstract class AbstractEntity
{
    protected $id;
    protected $fieldsList;
    /** @var \DeltaDb\Repository[] */
    private $managers = [];

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }

    /**
     * @return array
     */
    public function getFieldsList()
    {
        if (is_null($this->fieldsList)) {
            $methods = get_class_methods($this);
            $fields = [];
            foreach ($methods as $method) {
                if ($pos = strpos($method, "get") !== false && strpos($method, "Manager")===false) {
                    $field = substr($method, $pos+2);
                    if ($field !== "fieldsList") {
                        $fields[] = $field;
                    }
                }
            }
            $this->fieldsList = $fields;
        }
        return $this->fieldsList;
    }

    private function setManager($name, $object)
    {
        $this->managers[$name] = $object;
    }

    private function getManager($name)
    {
        return array_key_exists($name, $this->managers) ? $this->managers[$name] : null;
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
                        throw new \InvalidArgumentException("not found object for set manager");
                    }
                    $managerObject = $arguments[0];
                    return $this->setManager($managerName, $managerObject);
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