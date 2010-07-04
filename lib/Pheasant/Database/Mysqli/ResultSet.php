<?php

namespace Pheasant\Database\Mysqli;

class ResultSet extends Result implements \IteratorAggregate, \ArrayAccess
{
	private $_result;

	public function __construct($link, $result)
	{
		parent::__construct($link);
		$this->_result = $result;
		$this->_iterator = new ResultIterator($this->_result);
	}

	public function setHydrator($closure)
	{
		$this->_iterator->setHydrator($closure);
		return $this;
	}

	public function getIterator()
	{
		return $this->_iterator;
	}

	public function toArray()
	{
		$result = array();

		foreach($this as $row)
			$result[] = $row;

		return $result;
	}

	public function fetch()
	{
		if(!$this->_iterator->current())
			$this->_iterator->next();

		$value = $this->_iterator->current();
		$this->_iterator->next();
		return $value;
	}

	public function fetchOne()
	{
		$row = $this->fetch();
		return $row ? array_pop(array_values($row)) : null;
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
		throw new \BadMethodCallException('ResultSets are read-only');
	}

	public function offsetExists($offset)
	{
		$this->_iterator->seek($offset);
		return $this->_iterator->valid();
	}

	public function offsetUnset($offset)
	{
		throw new \BadMethodCallException('ResultSets are read-only');
	}
}
