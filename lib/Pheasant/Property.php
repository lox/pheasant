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

	public function closureGet($object)
	{
		return function($key) use($object) {
			return $object->get($key);
		};
	}

	public function closureSet($object)
	{
		return function($key, $value) use($object) {
			return $object->set($key, $value);
		};
	}

	public function closureAdd($object)
	{
		return function($value) use($object) {
			throw new \BadMethodCallException('Add not supported');
		};
	}

	public function closureRemove($object)
	{
		return function($key) use($object) {
			throw new \BadMethodCallException('Remove not supported');
		};
	}
}
