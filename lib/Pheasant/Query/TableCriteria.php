<?php

namespace Pheasant\Query;

/**
 * A helper for querying a table by criteria
 */
class TableCriteria extends Criteria
{
    private $_table;

    public function __construct($table, $where, $params=array())
    {
        parent::__construct($where, $params);
        $this->_table = $table;
    }

    public function update($data)
    {
        return $this->_table->update($data, $this);
    }

    public function replace($data)
    {
        return $this->_table->replace($data, $this);
    }

    public function upsert($data)
    {
        return $this->_table->upsert($data, $this);
    }

    public function delete()
    {
        return $this->_table->delete($this);
    }

    public function count()
    {
        return $this->_table->query()->select('COUNT(*)')->where($this)->scalar();
    }
}
