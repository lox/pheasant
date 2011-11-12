<?php

namespace Pheasant\Database\Mysqli;

/**
 * Encapsulates the result of executing a statement
 */
class ResultSet implements \IteratorAggregate, \ArrayAccess, \Countable
{
	private $_link, $_result, $_affected, $_hydrator;

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
		if(	$this->_result === false)
			return new \EmptyIterator(array());;

		if(!isset($this->_iterator))
		{
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
	 * Fetch the next row as an associative array
	 */
	public function fetch()
	{
		$iterator = $this->getIterator();

		if(!$iterator->current())
			$iterator->next();

		$value = $iterator->current();
		$iterator->next();
		return $value;
	}

	/**
	 * Fetch the next row, return the first column
	 */
	public function fetchOne()
	{
		$row = $this->fetch();
		return $row ? array_pop(array_values($row)) : null;
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
	 * The number of rows that the statement affected
	 * @return int
	 */
	public function affectedRows()
	{
		return $this->_affected;
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
