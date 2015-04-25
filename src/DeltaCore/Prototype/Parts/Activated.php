<?php
/**
 * User: Vasiliy Shvakin (orbisnull) zen4dev@gmail.com
 */

namespace DeltaCore\Prototype\Parts;


trait Activated {
    /**
     * @var bool
     */
    protected $active = true;

    /**
     * @return boolean
     */
    public function isActive()
    {
        return $this->active;
    }

    public function getActive()
    {
        return $this->isActive();
    }

    /**
     * @param boolean $active
     */
    public function setActive($active)
    {
        $this->active = $active;
    }
}
