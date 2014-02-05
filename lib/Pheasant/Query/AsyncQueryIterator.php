<?php

namespace Pheasant\Query;
use \Pheasant\Pheasant;
use \Pheasant\Query\QueryIterator;

/**
 * An iterator that lazily executes a query, hydrating as it goes
 */
class AsyncQueryIterator extends QueryIterator
{
    /**
     * Constructor
     * @param object An instance of Query
     * @param closure A closure that takes a row and returns an object
     */
    public function __construct($query, $hydrator=null)
    {
        $this->_query = $query;
        $this->_hydrator = $hydrator;
        $this->_resultSet = $query->asyncExecute();
    }

    /**
     * Returns the query result set iterator, executing the query if needed
     */
    protected function _resultSet()
    {
        return $this->_resultSet;
    }
}
