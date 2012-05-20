<?php

namespace Pheasant\Database\Mysqli;

/**
 * A collection of fields associated with a MySQL ResultSet 
 */
class Fields implements \IteratorAggregate, \Countable, \ArrayAccess
{
	private $_resultSet, $_fields=array(), $_fieldCount, $_iterator;

	public function __construct($resultSet)
	{
		$this->_resultSet = $resultSet;
		$this->_fieldCount = $resultSet ? $resultSet->field_count : 0;
	}		

	public function count()
	{
		return $this->_fieldCount;
	}

	public function getIterator()
	{
		if(!isset($this->_iterator))
		{
			if(empty($this->_fields) && $this->_fieldCount)
				$this->_fields = $this->_resultSet->fetch_fields();

			$this->_iterator = new \ArrayIterator($this->_fields);
		}

		return $this->_iterator;
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
		throw new \BadMethodCallException('Fields are read-only');
	}

	public function offsetExists($offset)
	{
		$this->getIterator()->seek($offset);
		return $this->getIterator()->valid();
	}

	public function offsetUnset($offset)
	{
		throw new \BadMethodCallException('Fields are read-only');
	}	
}
