<?php

namespace Pheasant\Locking;

/**
 * A blocking row-lock on an object
 */
class PessimisticLock
{
    private $_clause;

    /**
     * The domain object to be locked. This is set to the locked object
     * after acquiring the lock.
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
     * @param bool whether or not to throw an exception if the object's
     *        contents differ from the db
     */
    public function acquire($force=false)
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

        if(!$force && !$this->object->equals($freshObject)) {
            throw new StaleObjectException(sprintf(
                "Object is stale, keys [%s] have changed in the database",
                implode(', ', $this->object->diff($freshObject))
            ));
        }
        $this->object = $freshObject;
    }
}
