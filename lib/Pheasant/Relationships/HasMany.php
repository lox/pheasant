<?php

namespace Pheasant\Relationships;

use \Pheasant\Collection;
use \Pheasant\Relationship;

/**
 * A HasMany relationship represents a 1 to N relationship.
 */
class HasMany extends Relationship
{
    /**
     * Constructor
     */
    public function __construct($class, $local, $foreign=null)
    {
        parent::__construct($class, $local, $foreign);
    }

    /* (non-phpdoc)
     * @see Relationship::get()
     */
    public function get($object, $key, $cache=null)
    {
        $query = $this->query(
            "{$this->foreign}=?", $object->get($this->local));

        return new Collection($this->class, $query, $this->adder($object));
    }

    /* (non-phpdoc)
     * @see Relationship::add()
     */
    public function add($object, $value)
    {
        $newValue = $object->{$this->local};

        if($newValue instanceof PropertyReference)
            $value->saveAfter($object);

        $value->set($this->foreign, $newValue);
    }
}
