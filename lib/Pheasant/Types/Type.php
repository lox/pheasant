<?php

namespace Pheasant\Types;

class Type
{
	public $name, $length, $params;

	public function __construct($name, $length, $params)
	{
		$this->name = $name;
		$this->length = $length;
		$this->params = $params;
	}
}
