<?php

namespace Pheasant\Database\Mysqli;

/**
 * Filter an iterator that returns associate arrays down to just one 
 * of the keys
 */
class ColumnIterator extends \IteratorIterator
{
	private $_column;

	public function __construct($iterator, $column=null)
	{
		parent::__construct($iterator);
		$this->_column = $column;
	}

	public function current()
	{
		$row = parent::current();
		$column = $this->_column ?: array_shift(array_keys($row));

		return $row[$column];
	}
}
