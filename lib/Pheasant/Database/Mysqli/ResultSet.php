<?php

namespace Pheasant\Database\Mysqli;

/**
 * Encapsulates the result of executing a statement
 */
class ResultSet implements \IteratorAggregate, \ArrayAccess, \Countable
{
    private $_link, $_result, $_affected, $_hydrator, $_fields;

    /**
     * Constructor
     * @param $link MySQLi
     * @param $result MySQLi_Result
     */
    public function __construct($link, $result=false)
    {
        $this->_link = $link;
        $this->_result = $result;
        $this->_affected = $link->affected_rows;
    }

    public function setHydrator($callback)
    {
        $this->_hydrator = $callback;

        return $this;
    }

    /* (non-phpdoc)
     * @see IteratorAggregate::getIterator()
     */
    public function getIterator()
    {
        if ($this->_result === false) {
            return new \EmptyIterator();
        }

        if (!isset($this->_iterator)) {
            $this->_iterator = new ResultIterator($this->_result);
            $this->_iterator->setHydrator($this->_hydrator);
        }

        return $this->_iterator;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return iterator_to_array($this->getIterator());
    }

    /**
     * Returns the next available row as an associative array.
     * @return array or NULL on EOF
     */
    public function row()
    {
        $iterator = $this->getIterator();

        if(!$iterator->current())
            $iterator->next();

        $value = $iterator->current();
        $iterator->next();

        return $value;
    }

    /**
     * Returns the nth column from the current row.
     * @return scalar or NULL on EOF
     */
    public function scalar($idx=0)
    {
        $row = $this->row();

        if(is_null($row))

            return NULL;

        $values = is_numeric($idx) ? array_values($row) : $row;

        return $values[$idx];
    }

    /**
     * Fetches an iterator that only returns a particular column, defaults to the
     * first
     * @return Iterator
     */
    public function column($column=null)
    {
        return new ColumnIterator($this->getIterator(), $column);
    }

    /**
     * Seeks to a particular row offset
     * @chainable
     */
    public function seek($offset)
    {
        $this->getIterator()->seek($offset);

        return $this;
    }

    /**
     * The number of rows that the statement affected
     * @return int
     */
    public function affectedRows()
    {
        return $this->_affected;
    }

    /**
        * The fields returned in the result set as an array of fields
        * @return Fields object
      */
    public function fields()
    {
        if(!isset($this->_fields))
            $this->_fields = new Fields($this->_result);

        return $this->_fields;
    }

    /**
     * The number of rows in the result set, or the number of affected rows
     */
    public function count()
    {
        return $this->_affected;
    }

    /**
     * The last auto_increment value generated in the statement
     */
    public function lastInsertId()
    {
        return $this->_link->insert_id;
    }

    // ----------------------------------
    // array access

    public function offsetGet($offset)
    {
        $this->getIterator()->seek($offset);

        return $this->getIterator()->current();
    }

    public function offsetSet($offset, $value)
    {
        throw new \BadMethodCallException('ResultSets are read-only');
    }

    public function offsetExists($offset)
    {
        $this->getIterator()->seek($offset);

        return $this->getIterator()->valid();
    }

    public function offsetUnset($offset)
    {
        throw new \BadMethodCallException('ResultSets are read-only');
    }
}
