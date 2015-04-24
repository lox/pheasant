<?php

namespace Pheasant\Query;
use \Pheasant\Pheasant;

/**
 * An iterator that lazily executes a query, hydrating as it goes
 */
class QueryIterator implements \SeekableIterator, \Countable
{
    private $_query;
    private $_hydrator;
    private $_iterator;
    private $_resultSet;
    private $_before=array();

    /**
     * Constructor
     * @param object An instance of Query
     * @param closure A closure that takes a row and returns an object
     */
    public function __construct($query, $hydrator=null)
    {
        $this->_query = $query;
        $this->_hydrator = $hydrator;
    }

    /**
     * Sets a hydrator to be used
     * @param closure A closure that takes a row and returns an object
     * @chainable
     */
    public function setHydrator($hydrator)
    {
        $this->_hydrator = $hydrator;

        return $this;
    }

    /**
     * Add a callback to be called with the query before it's executed
     * @chainable
     */
    public function before($callback)
    {
        $this->_before []= $callback;

        return $this;
    }

    /**
     * Returns the query result set iterator, executing the query if needed
     */
    private function _resultSet()
    {
        if (!isset($this->_resultSet)) {
            foreach ($this->_before as $callback) {
                $callback($this->_query);
            }
            $this->_resultSet = $this->_query->execute();
        }

        return $this->_resultSet;
    }

    /**
     * Returns the delegate iterator from the resultset
     */
    private function _iterator()
    {
        if(!isset($this->_iterator))
            $this->_iterator = $this->_resultSet()->getIterator();

        return $this->_iterator;
    }

    /**
    * Rewinds the internal pointer
    */
    public function rewind()
    {
        return $this->_iterator()->rewind();
    }

    /**
    * Moves the internal pointer one step forward
    */
    public function next()
    {
        return $this->_iterator()->next();
    }

    /**
    * Returns true if the current position is valid, false otherwise.
    * @return bool
    */
    public function valid()
    {
        return $this->_iterator()->valid();
    }

    /**
    * Returns the row that matches the current position
    * @return array
    */
    public function current()
    {
        return $this->_hydrate($this->_iterator()->current());
    }

    /**
    * Returns the current position
    * @return int
    */
    public function key()
    {
        return $this->_iterator()->key();
    }

    /**
     * Seeks to a particular position in the result. Offset is from 0.
     */
    public function seek($position)
    {
        return $this->_iterator()->seek($position);
    }

    /**
     * Counts the number or results in the query
     */
    public function count()
    {
        return $this->_query->count();
    }

    /**
     * Hydrates a row into an object
     */
    private function _hydrate($row)
    {
        $callback = $this->_hydrator;

        return isset($this->_hydrator) ? call_user_func($callback, $row) : $row;
    }
}
