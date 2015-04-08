<?php
/**
 * User: Vasiliy Shvakin (orbisnull) zen4dev@gmail.com
 */

namespace DeltaCore\Prototype\Parts;


trait TimeStamp
{
    /** @var  \DateTime */
    protected $created;
    /** @var  \DateTime */
    protected $changed;

    /**
     * @return \DateTime
     */
    public function getCreated()
    {
        if (!is_null($this->created) and $this->created instanceof \DateTime) {
            $this->created = new \DateTime($this->created);
        }
        return $this->created;
    }

    /**
     * @param \DateTime|string $created
     */
    public function setCreated($created)
    {
        $this->created = $created;
    }

    /**
     * @return \DateTime
     */
    public function getChanged()
    {
        if (!is_null($this->changed) and $this->changed instanceof \DateTime) {
            $this->changed = new \DateTime($this->changed);
        }
        return $this->changed;
    }

    /**
     * @param \DateTime|string $changed
     */
    public function setChanged($changed)
    {
        $this->changed = $changed;
    }


}