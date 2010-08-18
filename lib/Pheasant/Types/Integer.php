<?php

namespace Pheasant\Types;

class Integer extends Type
{
	public function __construct($length=11, $params)
	{
		parent::__construct('integer', $length, $params);
	}
}
