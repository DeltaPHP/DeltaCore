<?php

namespace DeltaCore\Prototype\Parts;


trait CreatedTrait
{
    /** @var  \DateTime */
    protected $created;

    /**
     * @return \DateTime
     */
    public function getCreated()
    {
        if (null === $this->created) {
            $this->created = new \DateTime();
        } elseif(!$this->created instanceof \DateTime) {
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
}