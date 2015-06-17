<?php
/**
 * User: Vasiliy Shvakin (orbisnull) zen4dev@gmail.com
 */

namespace DeltaCore\Prototype;


use DeltaCore\Parts\MagicSetGetManagers;
use DeltaUtils\StringUtils;

abstract class AbstractEntity implements ArrayableInterface, StringableIterface, ElasticEntityInterface
{
    use MagicSetGetManagers;

    protected $id;
    protected $fieldsList;

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

    public function toArray()
    {
        $fields = $this->getFieldsList();
        $values = [];
        foreach ($fields as $field) {
            $method = "get" . ucfirst($field);
            if(method_exists($this, $method))
            $value = $this->{$method}();
            if (is_object($value)) {
                if (method_exists($value, "toArray")) {
                    $value = $value->toArray();
                } elseif (method_exists($this, "__toString")) {
                    $value = (string) $value;
                } else {
                    continue;
                }
            } elseif (is_resource($value)) {
                continue;
            }
            $values[$field] = $value;
        }
        return $values;
    }

    public function __toString()
    {
        $arrayData = $this->toArray();
        return StringUtils::toString($arrayData, true);
    }

    public function toElastic()
    {
        return $this->toArray();
    }

    public function getElasticOptions()
    {
        return [];
    }


}

