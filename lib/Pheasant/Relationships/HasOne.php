<?php

namespace Pheasant\Relationships;
use \Pheasant\PropertyReference;

/**
 * A HasOne relationship represents a 1->1 relationship. The foreign domain object
 * is responsible for maintaining a key referencing a local attribute.
 */
class HasOne extends RelationshipType
{
    /**
     * Constructor
     */
    public function __construct($class, $local, $foreign=null)
    {
        parent::__construct($class, $local, $foreign);
    }

    /* (non-phpdoc)
     * @see RelationshipType::get()
     */
    public function get($object, $key)
    {
        $result = $this
            ->query("{$this->foreign}=?", $object->{$this->local})
            ->execute();
            ;

        // TODO: is this the correct behaviour?
        if(!count($result))
            throw new \Pheasant\Exception("Failed to find a $key (via $this->foreign)");

        return $this->hydrate($result->row(), true);
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
