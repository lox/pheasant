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
        $keys = array_keys($row);
        $column = $this->_column ?: array_shift($keys);

        return $row[$column];
    }

    public function toArray()
    {
        return iterator_to_array($this);
    }

    public function unique()
    {
        return array_unique($this->toArray());
    }
}
