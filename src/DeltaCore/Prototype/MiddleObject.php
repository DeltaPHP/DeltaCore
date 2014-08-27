<?php
/**
 * User: Vasiliy Shvakin (orbisnull) zen4dev@gmail.com
 */

namespace DeltaCore\Prototype;

class MiddleObject extends AbstractEntity
{
    protected $name;
    protected $description;
    protected $created;
    protected $changed;
    protected $fieldsList;

    function __construct()
    {
        $this->setCreated(new \DateTime());
        $this->setChanged(new \DateTime());
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
        if (is_null($changed)) {
            return;
        }
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
        if (is_null($created)) {
            return;
        }
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
}