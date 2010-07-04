<?php

namespace Pheasant\Database\Mysqli;

class Binder
{
	private $_escaper;
	private $_params=array();

	public function __construct($escaper=null)
	{
		$this->_escaper = $escaper ?: new Escaper();
	}

	public function bind($sql, $params=array())
	{
		$this->_params = is_array($params)
			? $params
			: array($params)
			;

		return preg_replace_callback('/\?/',array($this,'_bind'),$sql);
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
