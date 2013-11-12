<?php

namespace Pheasant\Locking;

/**
 * A blocking row-lock on an object
 */
class PessimisticLock
{
    private $_object;

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
     * @return object the reloaded object
     */
    public function acquire()
    {
        if (!$this->_object->isSaved()) {
            throw new LockingException("Can't lock unsaved objects");
        }

        $finder = \Pheasant::instance()->finderFor($this->_object);

        return $freshObject = $finder
            ->find($this->_object->className(), $this->_object->identity()->toCriteria())
            ->lock($this->_clause)
            ->one()
            ;
    }
}
