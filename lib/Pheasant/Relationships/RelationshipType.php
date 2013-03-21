<?php

namespace Pheasant\Relationships;

class RelationshipType
{
    public $class, $local, $foreign;

    public function __construct($class, $local, $foreign=null)
    {
        $this->class = $class;
        $this->local = $local;
        $this->foreign = empty($foreign) ? $local : $foreign;
    }

    public function get($object, $key)
    {
        throw new \BadMethodCallException(
            "Get not supported on ".get_class($this));
    }

    public function set($object, $key, $value)
    {
        throw new \BadMethodCallException(
            "Set not supported on ".get_class($this));
    }

    public function add($object, $value)
    {
        throw new \BadMethodCallException(
            "Add not supported on ".get_class($this));
    }

    /**
     * Delegates to the finder for querying
     * @return Query
     */
    protected function query($sql, $params)
    {
        return \Pheasant::instance()->finderFor($this->class)
            ->query(new \Pheasant\Query\Criteria($sql, $params))
            ;
    }

    /**
     * Delegates to the schema for hydrating
     * @return DomainObject
     */
    protected function hydrate($row)
    {
        return \Pheasant::instance()
            ->schema($this->class)->hydrate($row);
    }

    /**
     * Helper function that creates a closure that calls the add function
     * @return Closure
     */
    protected function adder($object)
    {
        $rel = $this;

        return function($value) use ($object, $rel) {
            return $rel->add($object, $value);
        };
    }
}
