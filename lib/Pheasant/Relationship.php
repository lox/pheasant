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

	public function closureGet($object)
	{
		return $this->type->closureGet($object);
	}

	public function closureSet($object)
	{
		return $this->type->closureSet($object);
	}

	public function closureAdd($object)
	{
		return $this->type->closureAdd($object);
	}

	public function closureRemove($object)
	{
		return $this->type->closureRemove($object);
	}
}
