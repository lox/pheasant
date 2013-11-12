<?php

namespace Pheasant\Locking;

/**
 * A blocking row-lock on an object
 */
class PessimisticLock
{
    private $_clause;

    /**
     * The domain object to be locked.
     */
    public $object;

    /**
     * Constructor
     */
    public function __construct($object, $clause=null)
    {
        $this->object = $object;
        $this->_clause = $clause;
    }

    /**
     * Acquire the lock on the object
     * @param callable $onObjectChanged A closure that is called for when the
     *        locked object differs from the original. Takes the original object
     *        and the locked object as its arguments.
     */
    public function acquire($onObjectChanged=null)
    {
        if(!$this->object->isSaved()) {
            throw new LockingException("Can't lock unsaved objects");
        }

        $finder = \Pheasant::instance()->finderFor($this->object);

        $freshObject = $finder
            ->find($this->object->className(), $this->object->identity()->toCriteria())
            ->lock($this->_clause)
            ->one()
            ;

        if(!$this->object->equals($freshObject) && $onObjectChanged) {
           call_user_func($onObjectChanged, $this->object, $freshObject);
        }
    }
}
