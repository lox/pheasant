<?php

namespace Pheasant\Database;

/**
 * Encapsulates parameter binding and quoting
 */
class Binder
{
	private $_params=array();

	/**
	 * Takes a fragment of sql with bind params (?) in it and binds in the
	 * provided parameters.
	 * @return string
	 */
	public function bind($sql, $params=array())
	{
		$this->_params = (array) $params;

		return preg_replace_callback('/\?/', array($this,'_bindCallback'), $sql);
	}

	/**
	 * called by preg_replace_callback
	 */
	private function _bindCallback($match)
	{
		$param = array_shift($this->_params);

		// numerics and nulls don't require quoting
		if(is_int($param) || is_float($param))
			return $param;
		else if(is_null($param))
			return 'NULL';

		return $this->quote($this->escape($param));
	}

	/**
	 * Escapes any characters not allowed in an SQL string
	 * @return string
	 */
	public function escape($string)
	{
		return addslashes($string);
	}

	/**
	 * Surrounds a string with quote marks
	 * @return string
	 */
	public function quote($string)
	{
		return sprintf("'%s'", $string);
	}
}
