<?php
/**
 * User: Vasiliy Shvakin (orbisnull) zen4dev@gmail.com
 */

namespace DeltaCore\Prototype;


use DeltaCore\Parts\MagicSetGetManagers;
use DeltaDb\EntityInterface;
use DeltaUtils\Object\Prototype\StringableInterface;
use DeltaUtils\StringUtils;
use DeltaUtils\Object\Prototype\ArrayableInterface;

abstract class AbstractEntity implements EntityInterface, ArrayableInterface, StringableInterface, ElasticEntityInterface
{
    use MagicSetGetManagers;

    protected $id;
    protected $fieldsList;
    protected $systemFields = ["fieldsList", "elasticOptions", "systemFields", "notExportFields"];
    protected $notExportFields = [];
    protected $untrusted = false;

    public function setId($id)
    {
        $this->id = (integer) $id;
    }

    public function getId()
    {
        return $this->id;
    }

    /**
     * @return boolean
     */
    public function isUntrusted()
    {
        return $this->untrusted;
    }

    public function isTrusted()
    {
        return !$this->untrusted;
    }

    /**
     * @param boolean $untrusted
     */
    public function setUntrusted($untrusted = true)
    {
        $this->untrusted = $untrusted;
    }

    /**
     * @return array
     */
    protected function getSystemFields()
    {
        return $this->systemFields;
    }

    /**
     * @return array
     */
    protected function getNotExportFields()
    {
        return $this->notExportFields;
    }

    /**
     * @return array
     */
    public function getFieldsList()
    {
        if (is_null($this->fieldsList)) {
            $methods = get_class_methods($this);
            $fields = [];
            $systemFields = $this->getSystemFields();
            foreach ($methods as $method) {
                if ($pos = strpos($method, "get") !== false && strpos($method, "Manager")===false) {
                    $field = lcfirst(substr($method, $pos+2));
                    if (!in_array($field, $systemFields)) {
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
        $notExportFields = $this->getNotExportFields();
        foreach ($fields as $field) {
            if (in_array($field, $notExportFields)) {
                continue;
            }
            $method = "get" . ucfirst($field);
            if(is_callable([$this, $method]) ) {
                $value = $this->{$method}();
                if (is_object($value)) {
                    if ($value instanceof ArrayableInterface || method_exists($value, "toArray")) {
                        $value = $value->toArray();
                    } elseif ($value instanceof StringableInterface || method_exists($value, "__toString")) {
                        $value = (string)$value;
                    } else {
                        continue;
                    }
                } elseif (is_resource($value)) {
                    continue;
                }
                $values[$field] = $value;
            }
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

    public static function getElasticOptions()
    {
        return [
            '_source' => [
                'enabled' => true
            ],
            'properties' => [
                'id' => [
                    'type' => 'integer',
                ]
            ]
        ];
    }
}

