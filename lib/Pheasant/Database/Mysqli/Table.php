<?php

namespace Pheasant\Database\Mysqli;

use \Pheasant\Query\Criteria;

/**
 * A mysql table
 */
class Table
{
	private $_name, $_parsed, $_connection, $_columns;

	/**
	 * Constructor
	 * @param $name string the name of the table either db.name or name
	 */
	public function __construct($name, $connection)
	{
		$this->_name = $name;
		$this->_connection = $connection;
	}

	/**
	 * Creates the table, fails if the table exists
	 * @param $columns a map defining columns to Type objects
	 */
	public function create($columns, $options='charset=utf8 engine=innodb')
	{
		$types = new TypeMap($columns);
		$sql = sprintf(
			'CREATE TABLE %s (%s) %s',
			$this->_quoteName(),
			implode(', ', $types->columnDefs()),
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
		$this->_connection->execute(sprintf('DROP TABLE %s', $this->_quoteName()));
		return $this;
	}

	/**
	 * Truncates the table
	 * @chainable
	 */
	public function truncate()
	{
		$this->_connection->execute(sprintf('TRUNCATE TABLE %s', $this->_quoteName()));
		return $this;
	}

	/**
	 * Determines if the table exists (name only, column definition not checked)
	 */
	public function exists()
	{
		$parsed = $this->_parseName();
		$sql = 'SELECT count(*) FROM INFORMATION_SCHEMA.TABLES WHERE Table_Name=? ';
		$params = array($parsed->table);

		if (is_null($parsed->db))
		{
			$sql .= 'AND TABLE_SCHEMA=database() ';
		}
		else
		{
			$sql .= 'AND TABLE_SCHEMA=? ';
			$params []= $parsed->db;
		}

		return (bool) $this->_connection->execute($sql, $params)->scalar();
	}

	/**
	 * Returns all the database columns in SHOW COLUMN format
	 * @return array
	 */
	public function columns()
	{
		if(!isset($this->_columns))
		{
			$this->_columns = array();

			foreach($this->_connection->execute("SHOW COLUMNS FROM ".$this->_quoteName()) as $c)
			{
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
			$this->_quoteName(),
			$this->_buildSet($data)
			), array_values($data)
		);
	}

	/**
	 * Updates a row into the table
	 */
	public function update($data, Criteria $where)
	{
		if(empty($data))
			throw new Exception("Can't insert an empty row");

		return $this->_connection->execute(sprintf(
			'UPDATE %s SET %s WHERE %s',
			$this->_quoteName(),
			$this->_buildSet($data),
			$where
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
			$this->_quoteName(),
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
			$this->_quoteName(),
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
			$this->_quoteName(),
			$this->_buildSet($data)
			), array_values($data)
		);
	}


	/**
	 * Builds a Query object for the table
	 */
	public function query()
	{
		$query = new \Pheasant\Query\Query($this->_connection);
		return $query->from($this->_name);
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
		$columns = '';

		foreach($data as $key=>$value)
			$columns[] = sprintf('`%s`=?',$key);

		return implode(', ', $columns);
	}

	/**
	 * Parse tablename or database.tablename into object with table and db props
	 * If dbname is not present, db == null
	 * @return object
	 */
	private function _parseName()
	{
		if(!isset($this->_parsed))
		{
			$tokens = explode('.', $this->_name, 2);
			$this->_parsed = (object) array(
				'table' => array_pop($tokens),
				'db' => array_pop($tokens)
			);
		}

		return $this->_parsed;
	}

	/**
	 * Backtick quotes the table name like `table`
	 * or `database`.`table` when a db name is present
	 * @return string
	 */
	private function _quoteName()
	{
		if(!isset($this->_quoted))
		{
			$parsed = $this->_parseName();

			// only specify db name if we need to
			$this->_quoted = (!is_null($parsed->db))
				? sprintf('`%s`.`%s`', $parsed->db, $parsed->table)
				: sprintf('`%s`', $parsed->table)
				;
		}

		return $this->_quoted;
	}
}
