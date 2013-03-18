<?php

namespace Pheasant\Database\Mysqli;

class ResultIterator implements \SeekableIterator, \Countable
{
	private $_result;
	private $_position;
	private $_currentRow;
	private $_hydrator;
	private $_fetchMode = MYSQLI_ASSOC;

	/**
	* Constructor
	* @param MySQLi_Result $result
	*/
	public function __construct($result)
	{
		$this->_result = $result;
	}

	/**
	* Destructor
	* Frees the Result object
	*/
	public function __destruct()
	{
		$this->_result->free();
	}

	/**
	 * Sets a hydrator
	 */
	public function setHydrator($callback)
	{
		$this->_hydrator = $callback;
	}

	/**
	 * Set mysqli resultset fetchmode
	 * @param int [MYSQLI_ASSOC | MYSQLI_NUM | MYSQLI_BOTH]
	 */
	public function setFetchMode($mode)
	{
		$this->_fetchMode = $mode;
		return $this;
	}

	/**
	 * Return mysqli resultset fetchmode
	 * @return int [MYSQLI_ASSOC | MYSQLI_NUM | MYSQLI_BOTH]
	 */
	public function getFetchMode()
	{
		return $this->_fetchMode;
	}

	/**
	* Rewinds the internal pointer
	*/
	public function rewind()
	{
		$this->seek(0);
	}

	/**
	* Moves the internal pointer one step forward
	*/
	public function next()
	{
		$this->_currentRow = $this->_fetch();
		$this->_position = ++$this->_position;
	}

	/**
	* Returns true if the current position is valid, false otherwise.
	* @return bool
	*/
	public function valid()
	{
		return $this->_position < $this->_result->num_rows;
	}

	/**
	* Returns the row that matches the current position
	* @return array
	*/
	public function current()
	{
		return $this->_currentRow;
	}

	/**
	* Returns the current position
	* @return int
	*/
	public function key()
	{
		return $this->_position;
	}

	/**
	 * Seeks to a particular position in the result
	 */
	public function seek($position)
	{
		if($this->_position !== $position)
		{
			$this->_result->data_seek($this->_position = $position);
			$this->_currentRow = $this->_fetch();
			$this->_position = $position;
		}
	}

	/**
	 * Returns the number of rows in the result
	 */
	public function count()
	{
		return $this->_result->num_rows;
	}

	/**
	 * Template for fetching the array
	 */
	private function _fetch()
	{
		return isset($this->_hydrator)
			? call_user_func($this->_hydrator, $this->_result->fetch_array($this->_fetchMode))
		  : $this->_result->fetch_array($this->_fetchMode)
			;
	}
}

