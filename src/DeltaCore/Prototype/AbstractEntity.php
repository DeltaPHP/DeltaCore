<?php
/**
 * User: Vasiliy Shvakin (orbisnull) zen4dev@gmail.com
 */

namespace DeltaCore\Prototype;


abstract class AbstractEntity
{
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
                if ($pos = strpos($method, "get") !== false) {
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


} 