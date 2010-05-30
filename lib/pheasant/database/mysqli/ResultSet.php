<?php

namespace pheasant\database\mysqli;

class ResultSet extends Result implements \IteratorAggregate, \ArrayAccess
{
	private $_result;

	public function __construct($link, $result)
	{
		parent::__construct($link);
		$this->_result = $result;
		$this->_iterator = new ResultIterator($this->_result);
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
