<?php

namespace Pheasant\Database\Mysqli;

/**
 * A collection of fields associated with a MySQL ResultSet
 */
class Fields implements \IteratorAggregate, \Countable, \ArrayAccess
{
    private $_resultSet, $_count, $_fields=array();

    public function __construct($resultSet)
    {
        $this->_resultSet = $resultSet;
        $this->_count = $resultSet ? $resultSet->field_count : 0;
    }

    public function count()
    {
        return $this->_count;
    }

    public function getIterator()
    {
        $fields = array();

        // make sure we have all of the lazy-loaded fields
        for($i=0; $i<$this->_count; $i++)
            $fields[$i] = $this->offsetGet($i);

        return new \ArrayIterator($fields);
    }

    // ----------------------------------
    // array access

    public function offsetGet($offset)
    {
        if($offset >= $this->_count)
            throw new \OutOfRangeException("No field exists at offset $offset");

        if(!isset($this->_fields[$offset]))
            $this->_fields[$offset] = $this->_resultSet->fetch_field_direct($offset);

        return $this->_fields[$offset];
    }

    public function offsetSet($offset, $value)
    {
        throw new \BadMethodCallException('Fields are read-only');
    }

    public function offsetExists($offset)
    {
        return $offset < $this->_count;
    }

    public function offsetUnset($offset)
    {
        throw new \BadMethodCallException('Fields are read-only');
    }
}
