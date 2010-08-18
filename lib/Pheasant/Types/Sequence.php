<?php

namespace Pheasant\Types;

class Sequence extends Integer
{
	public function __construct($params)
	{
		parent::__construct(11, $params);
	}
}
