<?php
/**
 * User: Vasiliy Shvakin (orbisnull) zen4dev@gmail.com
 */

namespace DeltaCore\Prototype;

use DeltaCore\Prototype\Parts\SeoFields;

class BaseEntityObject
{
    use SeoFields;

    protected $id;
    protected $name;
    protected $description;
    protected $created;
    protected $changed;

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getChanged()
    {
        if (!is_null($this->changed) && !$this->changed instanceof \DateTime) {
            $this->changed = new \DateTime($this->changed);
        }
        return $this->changed;
    }

    /**
     * @param mixed $changed
     */
    public function setChanged($changed)
    {
        $this->changed = $changed;
    }

    /**
     * @return mixed
     */
    public function getCreated()
    {
        if (!is_null($this->created) && !$this->created instanceof \DateTime) {
            $this->created = new \DateTime($this->created);
        }
        return $this->created;
    }


    public function setCreated($created)
    {
        $this->created = $created;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param mixed $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return array
     */
    public function getFieldsList()
    {
        $methods = get_class_methods($this);
        $fields = [];
        foreach($methods as $method) {
            if ($pos = strpos($method, "get") !== false) {
                $field = substr($method, $pos);
                $fields[] = $field;
            }
        }
        return $fields;
    }

}