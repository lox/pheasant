<?php

namespace Pheasant\Database\Mysqli;

/**
 * A mysql table
 */
class Table
{
	private $_name, $_connection;

	/**
	 * Constructor
	 */
	public function __construct($name, $connection)
	{
		$this->_name = $name;
		$this->_connection = $connection;
	}

	private function _nativeType($type)
	{
		$type = clone $type;
		$type->params = str_replace(
			array('primary', 'notnull', 'required', 'sequence'),
			array('primary key', 'not null', 'not null', ''),
			$type->params);

		switch($type->name)
		{
			case 'string': $type->name ='varchar'; break;
			case 'integer': $type->name ='int'; break;
			default: throw new Exception("Unknown type {$type->name}");
		}

		return $type;
	}

	/**
	 * Convert generic types into mysql colum definitions
	 */
	private function _columnDefinitions($columns)
	{
		$definitions = array();

		foreach($columns as $column=>$type)
		{
			$type = $this->_nativeType($type);
			$definitions[] = sprintf('`%s` %s(%d) %s',
				$column,
				$type->name,
				$type->length,
				$type->params
				);
		}

		return $definitions;
	}

	/**
	 * Creates the table, fails if the table exists
	 * @param $columns a map defining columns to Type objects
	 */
	public function create($columns, $options='charset=utf8 engine=innodb')
	{
		$this->_connection->execute(sprintf(
			'CREATE TABLE `%s` (%s) %s',
			$this->_name,
			implode(', ', $this->_columnDefinitions($columns)),
			$options
			));
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
		$this->_connection->execute(sprintf('DROP TABLE `%s`',$this->_name));
		return $this;
	}

	/**
	 * Truncates the table
	 * @chainable
	 */
	public function truncate()
	{
		$this->_connection->execute(sprintf('TRUNCATE TABLE `%s`',$this->_name));
		return $this;
	}

	/**
	 * Determines if the table exists (name only, column definition not checked)
	 */
	public function exists()
	{
		return (bool) $this->_connection->execute(
			'SELECT Table_Name from INFORMATION_SCHEMA.TABLES
			WHERE Table_Name=? and TABLE_SCHEMA=DATABASE()',
			$this->_name
			)->count();
	}

	/**
	 * Inserts a row into the table
	 */
	public function insert($data)
	{
		if(empty($data))
			throw new Exception("Can't insert an empty row");

		return $this->_connection->execute(sprintf(
			'INSERT INTO `%s` (`%s`) VALUES (%s)',
			$this->_name,
			implode('`,`', array_keys($data)),
			implode(', ', array_fill(0, count($data), '?'))
			), array_values($data));
	}

	/**
	 * Updates a row into the table
	 */
	public function update($data, $keys)
	{
		if(empty($data))
			throw new Exception("Can't insert an empty row");

		$columns = '';
		$where = '';

		foreach($data as $key=>$value)
			$columns[] = sprintf('`%s`=?',$key);

		foreach($keys as $key=>$value)
			$where[] = sprintf('`%s`=?',$key);

		return $this->_connection->execute(sprintf(
			'UPDATE `%s` SET %s WHERE %s',
			$this->_name,
			implode(', ', $columns),
			implode(' AND ', $where)
			), array_merge(array_values($data), array_values($keys)));
	}
}
