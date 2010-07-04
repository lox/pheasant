<?php

namespace Pheasant\Database\Mysqli;

class Escaper
{
	private $_link;

	public function __construct($link=null)
	{
		$this->_link = $link;
	}

	public function escape($string)
	{
		return $this->_link
			? $this->_link->escape_string($string)
			: addslashes($string)
			;
	}

	public function quote($string)
	{
		return sprintf("'%s'", $string);
	}
}
