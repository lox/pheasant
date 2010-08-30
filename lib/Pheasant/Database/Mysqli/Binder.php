<?php

namespace Pheasant\Database\Mysqli;

/**
 * A binder that makes use of internal mysql string escaping
 */
class Binder extends \Pheasant\Database\Binder
{
	private $_link;

	public function __construct($link)
	{
		$this->_link = $link;
	}

	public function escape($string)
	{
		return is_null($string) ? $string : $this->_link->escape_string($string);
	}
}
