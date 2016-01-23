<?php

namespace DeltaCore\Prototype\Parts;


trait ChangedTrait
{
    /** @var  \DateTime */
    protected $changed;

    /**
     * @return \DateTime
     */
    public function getChanged()
    {
        if (!is_null($this->changed) && !$this->changed instanceof \DateTime) {
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
