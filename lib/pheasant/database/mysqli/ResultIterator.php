<?php

namespace pheasant\database\mysqli;

class ResultIterator implements \SeekableIterator
{
	private $_result;
	private $_fetchMode;
	private $_position;
	private $_currentRow;

	/**
	* Constructor
	* @param MySQLi_Result $result
	*/
	public function __construct($result, $fetchMode=MYSQLI_ASSOC)
	{
		$this->_result = $result;
		$this->_fetchMode = $fetchMode;
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
		$this->_currentRow = $this->_result->fetch_array($this->_fetchMode);
		++$this->_position;
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
			$this->_currentRow = $this->_result->fetch_array($this->_fetchMode);
		}
	}
}

