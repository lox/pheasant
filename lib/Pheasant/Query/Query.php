<?php

namespace Pheasant\Query;
use \Pheasant;

/**
 * A query builder for generating SQL '92 SELECT statements
 */
class Query
{
	// query builder
	private $_select='*';
	private $_from=array();
	private $_joins=array();
	private $_limit=null;
	private $_where;

	// resultset
	private $_connection;
	private $_resultset;

	/**
	 * Constructor
	 */
	public function __construct($connection=null)
	{
		$this->_connection = $connection ?: Pheasant::instance()->connection();
	}

	/**
	 * Sets the SELECT clause, either a single column, an array or varargs.
	 * @chainable
	 */
	public function select($table)
	{
		$this->_select = $this->_arguments(func_get_args());
		return $this;
	}

	/**
	 * Sets the FROM clause, either a single table, an array or varargs.
	 * @chainable
	 */
	public function from($table)
	{
		$this->_from = $this->_arguments(func_get_args());
		return $this;
	}

	/**
	 * Sets the where clause to the provided sql, optionally binding
	 * parameters into the string.
	 * @chainable
	 */
	public function where($sql=null, $params=array())
	{
		$this->_where = new Criteria($sql, $params);
		return $this;
	}

	/**
	 * Adds an extra criteria to the where clause with an AND
	 * @chainable
	 */
	public function andWhere($sql=null, $params=array())
	{
		if(!isset($this->_where))
			$this->_where = new Criteria();

		$this->_where->and(new Criteria($sql, $params));
		return $this;
	}

	/**
	 * Adds an extra criteria to the where clause with an OR
	 * @chainable
	 */
	public function orWhere($sql=null, $params=array())
	{
		if(!isset($this->_where))
			$this->_where = new Criteria();

		$this->_where->or(new Criteria($sql, $params));
		return $this;
	}

	/**
	 * Adds an INNER JOIN clause, either with a {@link Query} object or raw sql
	 * @chainable
	 */
	public function innerJoin($mixed, $criteria, $derived='derived')
	{
		return $this->_join('INNER JOIN', $mixed, $criteria, $derived);
	}

	/**
	 * Adds a LEFT JOIN clause, either with a {@link Query} object or raw sql
	 * @chainable
	 */
	public function leftJoin($mixed, $criteria, $derived='derived')
	{
		return $this->_join('LEFT JOIN', $mixed, $criteria, $derived);
	}

	/**
	 * Adds a RIGHT JOIN clause, either with a {@link Query} object or raw sql
	 * @chainable
	 */
	public function rightJoin($mixed, $criteria, $derived='derived')
	{
		return $this->_join('RIGHT JOIN', $mixed, $criteria, $derived);
	}

	/**
	 * Adds a limit clause
	 * @chainable
	 */
	public function limit($rows, $offset=0)
	{
		$this->_limit = sprintf("LIMIT %d OFFSET %d", $rows, $offset);
		return $this;
	}

	/**
	 * Returns the sql for the query
	 */
	public function toSql()
	{
		return implode(' ', array_filter(array(
			$this->_clause('SELECT', $this->_select),
			$this->_clause('FROM', $this->_from),
			implode(' ', $this->_joins),
			$this->_clause('WHERE', $this->_where),
			$this->_limit
			)));
	}

	public function __toString()
	{
		return $this->toSql();
	}

	/**
	 * Executes the query with the provided connection
	 * @return Result
	 */
	public function execute()
	{
		return $this->_connection->execute($this->toSql());
	}

	// -------------------------------------------
	// private helper methods

	private function _join($type, $mixed, $criteria, $derived='derived')
	{
		if(is_object($mixed))
			$mixed = sprintf('(%s) %s', $mixed, $derived);

		$this->_joins []= sprintf('%s %s %s', $type, $mixed, $criteria);
		return $this;
	}

	private function _clause($clause, $arg, $delim=', ')
	{
		if(!empty($arg))
		{
			$subject = is_array($arg) ? implode($delim, $arg) : $arg;
			return sprintf('%s %s', $clause, $subject);
		}
	}

	private function _arguments($args)
	{
		if(count($args) > 1)
		{
			return $args;
		}
		else
		{
			$arg = $args[0];
			return is_array($arg) ? $arg : array($arg);
		}
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
