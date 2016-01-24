<?php

namespace DeltaCore\Prototype\Parts;


trait ChangedTrait
{
    /** @var  \DateTime */
    protected $changed;

    /** @return \DateTime */
    abstract public function getCreated();

    /**
     * @return \DateTime
     */
    public function getChanged()
    {
        if (null === $this->changed) {
            $this->changed = $this->getCreated();
        } elseif(!$this->changed instanceof \DateTime) {
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
