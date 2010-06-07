<?php

namespace pheasant\query;
use \pheasant\Pheasant;

/**
 * Helper to construct sql
 */
class Query
{
	// query builder
	private $_select='*';
	private $_from=array();
	private $_where=array();
	private $_joins=array();
	private $_limit=null;

	// resultset
	private $_connection;
	private $_resultset;

	public function __construct($connection=null)
	{
		$this->_connection = $connection ?: Pheasant::connection();
	}

	public function select($fields)
	{
		$this->_select = $fields;
		return $this;
	}

	public function from($table)
	{
		$this->_from = $table;
		return $this;
	}

	public function where($sql, $params=array())
	{
		$this->_where = array($this->_connection->bind($sql, $params));
		return $this;
	}

	public function andWhere($sql, $params=array())
	{
		$this->_where[] = 'AND ('.$this->_connection->bind($sql, $params).')';
		return $this;
	}

	public function orWhere($sql, $params=array())
	{
		$this->_where[] = 'OR ('.$this->_connection->bind($sql, $params).')';
		return $this;
	}

	public function innerJoin($sql, $params=array())
	{
		$this->_joins[] = 'INNER JOIN '.$this->_connection->bind($sql, $params);
		return $this;
	}

	public function leftJoin($sql, $params=array())
	{
		$this->_joins[] = 'LEFT JOIN '.$this->_connection->bind($sql, $params);
		return $this;
	}

	public function limit($rows, $offset=0)
	{
		$this->_limit = sprintf("LIMIT %d OFFSET %d", $rows, $offset);
		return $this;
	}

	public function toSql()
	{
		$array = array(
			sprintf("SELECT %s", $this->_select),
			sprintf("FROM %s", $this->_from),
			implode(' ', $this->_joins),
			ltrim(implode(' ', $this->_where), 'ANDOR '),
			$this->_limit
			);

		return implode(' ',array_filter($array));
	}

	public function execute()
	{
		return $this->_connection->execute($this->toSql());
	}

	// -------------------------------------------
	// kicker methods execute the query

	public function count()
	{
		$query = clone $this;
		return $query
			->select("count(*) count")->limit(1)
			->execute()
			->fetchOne()
			;
	}
}
