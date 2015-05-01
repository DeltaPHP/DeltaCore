<?php
/**
 * User: Vasiliy Shvakin (orbisnull) zen4dev@gmail.com
 */

namespace DeltaCore\Prototype;


interface TimeStampInterface {
    public function getCreated();

    /**
     * @param \DateTime|string $created
     */
    public function setCreated($created);

    /**
     * @return \DateTime
     */
    public function getChanged();

    /**
     * @param \DateTime|string $changed
     */
    public function setChanged($changed);

}
