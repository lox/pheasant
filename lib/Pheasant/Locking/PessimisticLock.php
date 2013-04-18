<?php

namespace Pheasant\Locking;

/**
 * A blocking row-lock on an object
 */
class PessimisticLock
{
    private $_object, $_clause;

    /**
     * Constructor
     */
    public function __construct($object, $clause=null)
    {
        $this->_object = $object;
        $this->_clause = $clause;
    }

    /**
     * Acquire the lock on the object
     */
    public function acquire()
    {
        if(!$this->_object->isSaved()) {
            throw new LockingException("Can't lock unsaved objects");
        }

        $finder = \Pheasant::instance()->finderFor($this->_object);

        $freshObject = $finder
            ->find($this->_object->className(), $this->_object->identity()->toCriteria())
            ->lock($this->_clause)
            ->one()
            ;

        if(!$this->_object->equals($freshObject)) {
            throw new StaleObjectException("Object has changed in database");
        }
    }
}
