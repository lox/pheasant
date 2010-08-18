<?php

namespace Pheasant\Types;

class String extends Type
{
	public function __construct($length=255, $params)
	{
		parent::__construct('string', $length, $params);
	}
}
