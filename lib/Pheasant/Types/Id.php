<?php

namespace Pheasant\Types;

class Id extends Integer
{
	const ID_LENGTH = 11;

	public function __construct($options=null)
	{
		parent::__construct(self::ID_LENGTH, $options);
	}
}