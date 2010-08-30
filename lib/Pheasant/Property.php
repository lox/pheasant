<?php

namespace Pheasant;

class Property
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

	public function callGet($object, $key)
	{
		return $object->get($key);
	}

	public function callSet($object, $key, $value)
	{
		return $object->set($key, $value);
	}
}
