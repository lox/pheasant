<?php

namespace Pheasant\Query;

use \Pheasant;
use \Pheasant\Database\Binder;

/**
 * A builder object for simple sql where clauses.
 */
class Criteria
{
	private $_sql='';

	/**
	 * Constructor
	 * @param $where either a query string, or a key=>val array
	 * @param $params mixed, parameters to bind into the query string
	 */
	public function __construct($where=null, $params=array())
	{
		if(is_object($where))
		{
			$this->_sql = $where->toSql();
		}
		else if(is_array($where))
		{
			$boundWhere = array();
			foreach($where as $key=>$val)
				$boundWhere[] = $this->bind('`'.$key.'`'.'=?', array($val));
			$this->_sql = $this->_join('AND', $boundWhere);
		}
		else
		{
			$this->_sql = is_null($where) ? '' : $this->bind($where, (array)$params);
		}
	}

	/**
	 * Binds an array of parameters into a string
	 * @return string
	 */
	public function bind($sql, $params=array())
	{
		$binder = new Binder();
		return $binder->magicBind($sql, (array)$params);
	}

	/**
	 * Returns the sql representation of the where clause
	 */
	public function toSql()
	{
		return $this->_sql;
	}

	private function _join($token, $args)
	{
		return sprintf('(%s)',implode(" $token ", $args));
	}

	public function __toString()
	{
		return $this->toSql();
	}

	/**
	 * Triggers either the and() or or() methods
	 */
	public function __call($method, $params)
	{
		switch($method)
		{
			case 'and':
			case 'or':
				$this->_sql = $this->_join(strtoupper($method), $params);
				return $this;
			default:
				throw new \BadMethodCallException("Unknown method $method");
		}
	}
}
