<?php

namespace pheasant\query;
use \pheasant\Pheasant;

/**
 * An iterator that lazily executes a query and iterates over the results,
 * hydrating as it goes
 */
class QueryIterator implements \SeekableIterator, \Countable
{
	private $_query;
	private $_hydrator;
	private $_iterator;

	/**
	 * Constructor
	 * @param object An instance of Query
	 * @param mixed Either an object with a hydrate() method or a closure
	 */
	public function __construct($query, $hydrator)
	{
		$this->_query = $query;
		$this->_hydrator = $hydrator;
	}

	/**
	 * Returns the query result set iterator, executing the query if needed
	 */
	private function _resultSet()
	{
		if(!isset($this->_iterator))
			$this->_iterator = $this->_query->execute()->getIterator();

		return $this->_iterator;
	}

	/**
	* Rewinds the internal pointer
	*/
	public function rewind()
	{
		return $this->_resultSet()->rewind();
	}

	/**
	* Moves the internal pointer one step forward
	*/
	public function next()
	{
		return $this->_resultSet()->next();
	}

	/**
	* Returns true if the current position is valid, false otherwise.
	* @return bool
	*/
	public function valid()
	{
		return $this->_resultSet()->valid();
	}

	/**
	* Returns the row that matches the current position
	* @return array
	*/
	public function current()
	{
		return $this->_resultSet()->current();
	}

	/**
	* Returns the current position
	* @return int
	*/
	public function key()
	{
		return $this->_resultSet()->key();
	}

	/**
	 * Seeks to a particular position in the result
	 */
	public function seek($position)
	{
		return $this->_resultSet()->seek($position);
	}

	/**
	 * Counts the number or results in the query
	 */
	public function count()
	{
		return $this->_resultSet()->count();
	}
}
