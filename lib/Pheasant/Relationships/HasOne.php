<?php

namespace Pheasant\Relationships;

use \Pheasant\PropertyReference;
use \Pheasant\Relationship;

/**
 * A HasOne relationship represents a 1->1 relationship. The foreign domain object
 * is responsible for maintaining a key referencing a local attribute.
 */
class HasOne extends Relationship
{
    private $_allowEmpty;

    /**
     * Constructor
     */
    public function __construct($class, $local, $foreign=null, $allowEmpty=false)
    {
        parent::__construct($class, $local, $foreign);
        $this->_allowEmpty = $allowEmpty;
    }

    /* (non-phpdoc)
     * @see Relationship::get()
     */
    public function get($object, $key)
    {
        if(($localValue = $object->{$this->local}) === null)
            return null;

        $result = $this
            ->query("{$this->foreign}=?", $localValue)
            ->execute();
            ;

        if(!count($result)) {
            if($this->_allowEmpty) {
                return null;
            } else {
                throw new \Pheasant\Exception("Failed to find a $key (via $this->foreign)");
            }
        }

        return $this->hydrate($result->row());
    }

    /* (non-phpdoc)
     * @see Relationship::set()
     */
    public function set($object, $key, $value)
    {
        $newValue = $object->{$this->foreign};

        if($newValue instanceof PropertyReference)
            $object->saveAfter($value);

        $value->set($this->local, $newValue);
    }
}
