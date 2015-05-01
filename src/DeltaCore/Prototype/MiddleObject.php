<?php
/**
 * User: Vasiliy Shvakin (orbisnull) zen4dev@gmail.com
 */

namespace DeltaCore\Prototype;

use DeltaCore\Prototype\Parts\Activated;
use DeltaCore\Prototype\Parts\TimeStamp;

class MiddleObject extends AbstractEntity implements TimeStampInterface, ActivatedInterface
{
    use TimeStamp;
    use Activated;

    protected $name;
    protected $description;
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
