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

    public function toArray($oneField = false)
    {
        $fields = $this->getFieldsList();
        $values = [];
        foreach ($fields as $field) {
            $method = "get" . ucfirst($field);
            $value = $this->{$method}();
            if ($oneField) {
                $value = StringUtils::toString($value, $oneField);
            } else {
                if ($value instanceof AbstractEntity) {
                    $value = $value->toArray($oneField);
                }
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
}

