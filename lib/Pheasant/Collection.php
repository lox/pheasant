<?php

namespace Pheasant;

use \Pheasant;
use \Pheasant\Query\QueryIterator;

class Collection implements \IteratorAggregate, \Countable, \ArrayAccess
{
	private $_class;
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
		$this->_class = $class;
		$this->_add = $add;

		$this->_iterator = new QueryIterator($query, function($row) use($class) {
			return $class::fromArray($row, true);
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
