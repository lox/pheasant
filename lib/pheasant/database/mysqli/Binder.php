<?php

namespace pheasant\database\mysqli;

class Binder
{
	private $_placeholders;
	private $_sql;
	private $_params;
	private $_bound;
	private $_escaper;

	public function __construct($sql, $params, $escaper=null)
	{
		$this->_sql = $sql;
		$this->_params = $params;
		$this->_escaper = $escaper ? $escaper : new Escaper();
	}

	public function __toString()
	{
		if(!isset($this->_bound))
		{
			$this->_bound = preg_replace_callback(
				'/\?/',array($this,'_bind'),$this->_sql);
		}

		return $this->_bound;
	}

	private function _bind($match)
	{
		$param = array_shift($this->_params);

		if(is_int($param) || is_float($param))
			return $param;
		else if(is_null($param))
			return 'NULL';
		else
			return $this->_escaper->quote($this->_escaper->escape($param));
	}
}
