<?php

namespace pheasant;

class Future
{
	private $_object;
	private $_property;

	public function __construct($object, $property)
	{
		$this->_object = $object;
		$this->_property = $property;
	}

	public function __toString()
	{
		return $this->get();
	}

	public function get()
	{
		return (string) $this->_object->get($this->_property, null, false);
	}

	public function set($value)
	{
		$this->_object->set($this->_property);
		return $this;
	}
}
