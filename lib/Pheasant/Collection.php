<?php

namespace Pheasant;

use \Pheasant;
use \Pheasant\Query\QueryIterator;

class Collection implements \IteratorAggregate, \Countable, \ArrayAccess
{
    private $_query;
    private $_iterator;
    private $_add=false;
    private $_readonly=false;

    /**
     * @param $class string the classname to hydrate
     * @param $query Query the query object
     * @param $add Closure a closure to call when an object is appended
     */
    public function __construct($class, $query, $add=false)
    {
        $this->_query = $query;
        $this->_add = $add;

        $schema = $class::schema();
        $this->_iterator = new QueryIterator($query, function($row) use ($schema) {
            return $schema->hydrate($row);
        });
    }

    /**
     * Return one and one only object from a collection, throws an exception
     * if there is zero of >1 objects
     */
    public function one()
    {
        if($this->count() != 1)
            throw new Exception("Expected only 1 element, found ".$this->count());

        return $this->offsetGet(0);
    }

    public function last()
    {
        return $this->offsetGet($this->count());
    }

    public function toArray()
    {
        return iterator_to_array($this->_iterator);
    }

    /**
     * Adds a filter to the collection
     * @chainable
     */
    public function filter($sql, $params=array())
    {
        $this->_queryForWrite()->andWhere($sql, $params);

        return $this;
    }

    /**
     * Orders the collection
     * @chainable
     */
    public function order($sql, $params=array())
    {
        $this->_queryForWrite()->orderBy($sql, $params);

        return $this;
    }

    /**
     * Restricts the number of rows to return
     * @chainable
     */
    public function limit($rows, $offset=0)
    {
        $this->_queryForWrite()->limit($rows, $offset);

        return $this;
    }

    /**
     * Counts the number or results in the query
     */
    public function count()
    {
        return $this->_iterator->count();
    }

    /**
     * Selects only particular fields
     * @chainable
     */
    public function select($fields)
    {
        $this->_queryForWrite()->select($fields);

        return $this;
    }

    /**
     * Reduces a collection down to a single column
     * @chainable
     */
    public function column($field)
    {
        $query = clone $this->_queryForWrite();

        return $query->select($field)->execute()->column($field);
    }

    /**
     * Returns an iterator
     */
    public function getIterator()
    {
        $this->_readonly = true;

        return $this->_iterator;
    }

    /**
     * Filter function when called as a function
     */
    public function __invoke($sql, $params=array())
    {
        return $this->filter($sql, $params);
    }

    private function _queryForWrite()
    {
        if($this->_readonly)
            throw new Exception("Collection is read-only during iteration");

        return $this->_query;
    }

    // ----------------------------------
    // array access

    public function offsetGet($offset)
    {
        $this->_iterator->seek($offset);

        return $this->_iterator->current();
    }

    public function offsetSet($offset, $value)
    {
        if(empty($this->_add) && is_null($offset))
            throw new \BadMethodCallException('Add not supported');
        else if(is_null($offset))
            return call_user_func($this->_add, $value);
        else
            throw new \BadMethodCallException('Set not supported');
    }

    public function offsetExists($offset)
    {
        $this->_iterator->seek($offset);

        return $this->_iterator->valid();
    }

    public function offsetUnset($offset)
    {
        if(!isset($this->_accessor))
            throw new \BadMethodCallException('Unset not supported');
    }
}
