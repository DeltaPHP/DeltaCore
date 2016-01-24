<?php
/**
 * User: Vasiliy Shvakin (orbisnull) zen4dev@gmail.com
 */

namespace DeltaCore\Prototype;

use DeltaCore\Prototype\Parts\Activated;
use DeltaCore\Prototype\Parts\TimeStampTrait;

class MiddleObject extends AbstractEntity implements TimeStampInterface, ActivatedInterface
{
    use TimeStampTrait;
    use Activated;

    protected $name;
    protected $description;

    function __construct()
    {
        $this->setCreated(new \DateTime());
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
