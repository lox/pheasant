<?php

namespace Pheasant\Database\Mysqli;

class ResultIterator implements \SeekableIterator, \Countable
{
    private $_result;
    private $_position;
    private $_currentRow;
    private $_hydrator;

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
     * Seeks to a particular position in the result, offset is from 0.
     */
    public function seek($position)
    {
        if ($position < 0) {
            throw new \OutOfBoundsException("Unable to seek to negative offset $position");
        }

        if ($this->_position !== $position) {
            if(($count = $this->_result->num_rows) && ($position > ($count-1)))
                throw new \OutOfBoundsException("Unable to seek to offset $position");

            if ($count) {
                $this->_result->data_seek($this->_position = $position);
                $this->_currentRow = $this->_fetch();
            }

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
            ? call_user_func($this->_hydrator, $this->_result->fetch_array(MYSQLI_ASSOC))
          : $this->_result->fetch_array(MYSQLI_ASSOC)
            ;
    }

    public function toArray()
    {
        return iterator_to_array($this);
    }
}
