<?php

namespace pheasant;
use \pheasant\query\QueryIterator;

class Collection implements \IteratorAggregate, \Countable, \ArrayAccess
{
	private $_mapper;
	private $_query;
	private $_iterator;
	private $_readonly=false;

	public function __construct($mapper, $query)
	{
		$this->_mapper = $mapper;
		$this->_query = $query;
		$this->_iterator = new QueryIterator($query, $mapper);
	}

	/**
	 * Adds a filter to the collection
	 * @chainable
	 */
	public function filter($sql, $params=array())
	{
		if($this->_readonly)
			throw new Exception("Collection is read-only during iteration");

		$this->_query->andWhere($sql, $params);
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
	function __invoke($sql, $params=array())
	{
		return $this->filter($sql, $params);
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
		throw new \BadMethodCallException('Collections are read-only');
	}

	public function offsetExists($offset)
	{
		$this->_iterator->seek($offset);
		return $this->_iterator->valid();
	}

	public function offsetUnset($offset)
	{
		throw new \BadMethodCallException('Collections are read-only');
	}
}
