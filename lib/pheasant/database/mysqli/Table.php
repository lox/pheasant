<?php

namespace pheasant\database\mysqli;

/**
 * A mysql table
 */
class Table
{
	private $_columns=array();
	private $_connection;
	private $_name;

	/**
	 * Constructor
	 */
	public function __construct($name, $connection)
	{
		$this->_name = $name;
		$this->_connection = $connection;
	}

	/**
	 * Sets a column definition internally
	 */
	private function _setColumn($column, $options, $defaults)
	{
		$this->_columns[$column] = (object) array_merge($defaults,
			$this->_expandOptions($options));
		return $this;
	}

	/**
	 * Expands any options from int=>key to key=>true
	 */
	private function _expandOptions($options)
	{
		$result = array();

		foreach($options as $key=>$value)
		{
			$newKey = is_numeric($key) ? $value : $key;
			$newValue = is_numeric($key) ? true : $value;
			$result[$newKey] = $newValue;
		}

		return $result;
	}

	/**
	 * Creates an integer column type, by default signed
	 * @chainable
	 */
	public function integer($column, $length=4, $options=array())
	{
		return $this->_setColumn($column, $options, array(
			'type'=>'integer',
			'length'=>$length,
			'unsigned'=>false,
			'notnull'=>true,
			));
	}

	/**
	 * Creates an variable string column type up to 255 characters long
	 * @chainable
	 */
	public function string($column, $length=255, $options=array())
	{
		return $this->_setColumn($column, $options, array(
			'type'=>'varchar',
			'length'=>$length,
			'notnull'=>true,
			));
	}

	/**
	 * Creates a blob of text
	 * @chainable
	 */
	public function text($column, $length=null, $options=array())
	{
		return $this->_setColumn($column, $options, array(
			'type'=>'text',
			'length'=>null,
			'notnull'=>true,
			));
	}

	private function _columnDefinitions()
	{
		$definitions = array();

		foreach($this->_columns as $name=>$column)
		{
			$definition = sprintf(
				'`%s` %s(%d)',
				$name,
				$column->type,
				$column->length
				);

			foreach(array('auto_increment','unsigned','primary') as $key)
			{
				if(isset($column->$key) && $column->$key)
				{
					switch($key)
					{
						case 'primary': $definition .= ' PRIMARY KEY'; break;
						default: $definition .= strtoupper(" $key"); break;
					}
				}
			}

			$definitions[] = rtrim($definition);
		}

		return $definitions;
	}

	private function _tableOptions($options)
	{
		$tableOptions = array();
		$defaults = array(
			'charset'=>'utf8',
			'engine'=>'innodb',
			);

		foreach(array_merge($defaults, $options) as $key=>$value)
		{
			$tableOptions[] = sprintf("%s=%s",
				$this->_connection->escape($key),
				$this->_connection->escape($value)
				);
		}

		return $tableOptions;
	}

	/**
	 * Creates the table if it doesn't already exist
	 */
	public function createIfNotExists($options=array())
	{
		$this->_connection->execute(sprintf(
			'CREATE TABLE IF NOT EXISTS `%s` (%s) %s',
			$this->_name,
			implode(', ', $this->_columnDefinitions()),
			implode(' ', $this->_tableOptions($options))
			));
	}

	/**
	 * Creates the table, dropping it first if it exists
	 * @chainable
	 */
	public function create($options=array())
	{
		if($this->exists()) $this->drop();

		$this->_connection->execute(sprintf(
			'CREATE TABLE `%s` (%s) %s',
			$this->_name,
			implode(', ', $this->_columnDefinitions()),
			implode(' ', $this->_tableOptions($options))
			));
	}

	/**
	 * Drops the table
	 * @chainable
	 */
	public function drop()
	{
		$this->_connection->execute(
			sprintf('DROP TABLE `%s`',$this->_name));
		return $this;
	}

	/**
	 * Truncates the table
	 * @chainable
	 */
	public function truncate()
	{
		$this->_connection->execute(
			sprintf('TRUNCATE TABLE `%s`',$this->_name));
		return $this;
	}

	/**
	 * Determines if the table exists (name only, column definition not checked)
	 */
	public function exists()
	{
		return $this->_connection->execute(
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
