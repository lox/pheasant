<?php

namespace Pheasant\Database\Mysqli;

/**
 * A mysql table name
 */
class TableName
{
    public
        $table = NULL,
        $database = NULL;

    public function __construct($table)
    {
        // parse a fully qualified table name
        $tokens = explode('.', $table, 2);

        $this->table = array_pop($tokens);
        $this->database = array_pop($tokens);
    }

    public function __toString()
    {
        return (!is_null($this->database))
                ? sprintf('%s.%s', $this->database, $this->table)
                : sprintf('%s', $this->table);	}

    public function quoted()
    {
        return (!is_null($this->database))
                ? sprintf('`%s`.`%s`', $this->database, $this->table)
                : sprintf('`%s`', $this->table);
    }
}
