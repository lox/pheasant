<?php

namespace Pheasant\Types;

/**
 * A basic boolean type
 */
class Boolean extends Type
{
	const TYPE='boolean';

	/**
	 * Constructor
	 */
	public function __construct($params=null)
	{
		parent::__construct(self::TYPE, NULL, $params);
	}
}
