<?php

namespace Pheasant;

class Property
{
	public function __construct($options)
	{
		foreach($options as $key=>$value)
		{
			$this->{$key} = $value;
		}
	}

	public function __toString()
	{
		return $this->name;
	}
}
