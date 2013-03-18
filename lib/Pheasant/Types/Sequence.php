<?php

namespace Pheasant\Types;

class Sequence extends Id
{
	public $sequence;

	public function __construct($sequence=null, $params=null)
	{
		parent::__construct(sprintf("sequence=%s primary required %s",
			is_null($sequence) ? 'null' : $sequence, $params));

		$this->sequence = $sequence;
	}
}
