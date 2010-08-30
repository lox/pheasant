<?php

namespace Pheasant;

class Relationship
{
	public $name, $type;

	public function __construct($name, $type)
	{
		$this->name = $name;
		$this->type = $type;
	}

	public function __toString()
	{
		return $this->name;
	}

	// -------------------------------------
	// delegate double dispatch calls to type

	public function callGet($object, $key)
	{
		return $this->type->callGet($object, $key);
	}

	public function callSet($object, $key, $value)
	{
		return $this->type->callSet($object, $key, $value);
	}
}
