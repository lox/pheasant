<?php

namespace Pheasant\Database\Mysqli;

class Statement
{
	private $_link;
	private $_statement;
	private $_sql;

	public function __construct($link, $sql)
	{
		throw new \BadMethodCallException(__METHOD__.' not implemented');
	}
}
