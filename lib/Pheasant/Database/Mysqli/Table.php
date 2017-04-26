<?php

namespace Pheasant\Database\Mysqli;

use \Pheasant\Query\Criteria;

/**
 * A mysql table
 */
class Table
{
    private $_name, $_connection, $_columns;

    /**
     * Constructor
     * @param $name TableName
     */
    public function __construct($name, $connection)
    {
        $this->_name = ($name instanceof TableName) ? $name : new TableName($name);
        $this->_connection = $connection;
    }

    /**
     * Returns the name of the table as a TableName
     */
    public function name()
    {
        return $this->_name;
    }

    /**
     * Return the string name of the table
     */
    public function __toString()
    {
        return (string) $this->name();
    }

    /**
     * Creates the table, fails if the table exists
     * @param $columns a map defining columns to Type objects
     */
    public function create($columns, $options='charset=utf8 engine=innodb')
    {
        $columnSql = array();
        $platform = $this->_connection->platform();

        foreach($columns as $name=>$type)
            $columnSql []= $type->columnSql($name, $platform);

        $sql = sprintf('CREATE TABLE %s (%s) %s',
            $this->_name->quoted(),
            implode(', ', $columnSql),
            $options
        );

        $this->_connection->execute($sql);
    }

    /**
     * Creates the table if it doesn't exist
     */
    public function createIfNotExists($columns, $options='charset=utf8 engine=innodb')
    {
        if(!$this->exists())
            $this->create($columns, $options);
    }

    /**
     * Drops the table
     * @chainable
     */
    public function drop()
    {
        $this->_connection->execute(sprintf('DROP TABLE %s', $this->_name->quoted()));

        return $this;
    }

    /**
     * Drops the table if it exists
     * @chainable
     */
    public function dropIfExists()
    {
        $this->_connection->execute(sprintf('DROP TABLE IF EXISTS %s', $this->_name->quoted()));

        return $this;
    }

    /**
     * Truncates the table
     * @chainable
     */
    public function truncate()
    {
        $this->_connection->execute(sprintf('TRUNCATE TABLE %s', $this->_name->quoted()));

        return $this;
    }

    /**
     * Determines if the table exists (name only, column definition not checked)
     */
    public function exists()
    {
        $sql = 'SELECT count(*) FROM INFORMATION_SCHEMA.TABLES WHERE Table_Name=? ';
        $params = array($this->_name->table);

        if (is_null($this->_name->database)) {
            $sql .= 'AND TABLE_SCHEMA=database() ';
        } else {
            $sql .= 'AND TABLE_SCHEMA=? ';
            $params []= $this->_name->database;
        }

        return (bool) $this->_connection->execute($sql, $params)->scalar();
    }

    /**
     * Returns all the database columns in SHOW COLUMN format
     * @return array
     */
    public function columns()
    {
        if (!isset($this->_columns)) {
            $this->_columns = array();

            foreach ($this->_connection->execute("SHOW COLUMNS FROM ".$this->_name->quoted()) as $c) {
                $column = $c['Field'];
                unset($c['Field']);
                $this->_columns[$column] = $c;
            }
        }

        return $this->_columns;
    }

    /**
     * Inserts a row into the table
     */
    public function insert($data)
    {
        if(empty($data))
            throw new Exception("Can't insert an empty row");

        return $this->_connection->execute(sprintf(
            'INSERT INTO %s SET %s',
            $this->_name->quoted(),
            $this->_buildSet($data)
            ), array_values($data)
        );
    }

    /**
     * Updates a row into the table
     */
    public function update($data, Criteria $where, $limit=false)
    {
        if(empty($data))
            throw new Exception("Can't insert an empty row");

        return $this->_connection->execute(sprintf(
            'UPDATE %s SET %s WHERE %s%s',
            $this->_name->quoted(),
            $this->_buildSet($data),
            $where,
            $limit ? ' LIMIT '.intval($limit) : ''
            ), array_values($data)
        );
    }

    /**
     * Tries to update a record, or inserts if it doesn't exist. Worth noting
     * that affectedRows will be 2 on an update, 1 on an insert.
     */
    public function upsert($data)
    {
        if(empty($data))
            throw new Exception("Can't insert an empty row");

        return $this->_connection->execute(sprintf(
            'INSERT INTO %s SET %2$s ON DUPLICATE KEY UPDATE %2$s',
            $this->_name->quoted(),
            $this->_buildSet($data)
            ), array_merge(array_values($data),array_values($data))
        );
    }

    /**
     * Deletes rows in the table
     */
    public function delete($criteria=NULL)
    {
        $where = !is_null($criteria)
            ? 'WHERE '.$criteria->toSql()
            : NULL
            ;

        return $this->_connection->execute(sprintf(
            'DELETE FROM %s %s',
            $this->_name->quoted(),
            $where
            ));
    }

    /**
     * Inserts a row, or replaces it entirely if a row with the primary key exists
     * @see http://dev.mysql.com/doc/refman/5.0/en/replace.html
     */
    public function replace($data)
    {
        if(empty($data))
            throw new Exception("Can't replace an empty row");

        return $this->_connection->execute(sprintf(
            'REPLACE INTO %s SET %s',
            $this->_name->quoted(),
            $this->_buildSet($data)
            ), array_values($data)
        );
    }

    /**
     * Builds a Query object for the table
     */
    public function query($criteria=null)
    {
        $query = new \Pheasant\Query\Query($this->_connection);
        $query->from($this->_name);

        if(!is_null($criteria))
            $query->where($criteria);

        return $query;
    }

    /**
     * Builds a TableCriteria object for the table
     */
    public function where($where, $params=array())
    {
        return new \Pheasant\Query\TableCriteria($this, $where, $params);
    }

    /**
     * Builds a series of X=?, Y=?, Z=?
     */
    private function _buildSet($data)
    {
        $columns = [];

        foreach($data as $key=>$value)
            $columns[] = sprintf('`%s`=?',$key);

        return implode(', ', $columns);
    }
}
