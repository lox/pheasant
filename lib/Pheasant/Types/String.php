<?php

namespace Pheasant\Types;

/**
 * A basic string type
 */
class String extends Type
{
	const TYPE='string';

	/**
	 * Constructor
	 */
	public function __construct($length=255, $params=null)
	{
		parent::__construct(self::TYPE, $length, $params);
	}
}
