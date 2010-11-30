<?php

namespace Pheasant\Types;

/**
 * A fixed precision decimal type
 */
class Decimal extends Type
{
	const TYPE='decimal';

	public $scale;

	/**
	 * Constructor
	 */
	public function __construct($length=10, $scale=2, $params=null)
	{
		parent::__construct(self::TYPE, $length, $params);
		$this->scale = $scale;
	}
}
