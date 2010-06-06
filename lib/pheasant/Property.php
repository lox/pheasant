<?php

namespace pheasant;

class Property
{
	public function __construct($options)
	{
		foreach($options as $key=>$value)
		{
			$this->{$key} = $value;
		}
	}
}
