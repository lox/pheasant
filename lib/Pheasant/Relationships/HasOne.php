<?php

namespace Pheasant\Relationships;
use \Pheasant\PropertyReference;

/**
 * A HasOne relationship represents a 1->1 relationship. The foreign domain object
 * is responsible for maintaining a key referencing a local attribute.
 */
class HasOne extends RelationshipType
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
     * @see RelationshipType::get()
     */
    public function get($object, $key)
    {
        if(($localValue = $object->{$this->local}) === null)
            return null;

        if (isset($this->_cache[$localValue])) {
            return $this->_cache[$localValue];
        }

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
     * @see RelationshipType::set()
     */
    public function set($object, $key, $value)
    {
        $newValue = $object->{$this->foreign};

        if($newValue instanceof PropertyReference)
            $object->saveAfter($value);

        $value->set($this->local, $newValue);
    }
}
