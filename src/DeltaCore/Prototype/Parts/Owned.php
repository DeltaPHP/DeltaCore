<?php
/**
 * User: Vasiliy Shvakin (orbisnull) zen4dev@gmail.com
 */

namespace DeltaCore\Prototype\Parts;


use DeltaDb\EntityInterface;

/**
 * Class Owned
 * @package DeltaCore\Prototype\Parts
 * @method  \User\Model\UserManager getUserManager();
 */
trait Owned
{
    /**
     * @var \User\Model\User
     */
    protected $author;

    /**
     * @return \User\Model\User
     */
    public function getAuthor()
    {
        if (is_null($this->author)) {
            $this->author = $this->getUserManager()->getCurrentUser();
        }
        if (!is_null($this->author) && !$this->author instanceof EntityInterface) {
            $this->author = $this->getUserManager()->findById($this->author);
        }
        return $this->author;
    }

    /**
     * @param \User\Model\User $author
     */
    public function setAuthor($author)
    {
        $this->author = $author;
    }

}
