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
		$this->_params = is_array($params) ? $params : array($params);
		return preg_replace_callback('/\?/', array($this,'_bindCallback'), $sql);
	}

	/**
	 * called by preg_replace_callback
	 */
	private function _bindCallback($match)
	{
		return $this->quote($this->escape(array_shift($this->_params)));
	}

	/**
	 * Escapes any characters not allowed in an SQL string
	 * @return string
	 */
	public function escape($string)
	{
		return is_null($string) ? $string : addslashes($string);
	}

	/**
	 * Surrounds a string with quote marks, null is returned as NULL
	 * @return string
	 */
	public function quote($string)
	{
		return is_null($string) ? 'NULL' : sprintf("'%s'", $string);
	}
}
