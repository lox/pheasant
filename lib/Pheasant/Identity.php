<?php

namespace Pheasant;

class Identity implements \IteratorAggregate
{
	private $_properties, $_object;

	public function __construct($properties, $object)
	{
		$this->_properties = $properties;
		$this->_object = $object;
	}

	public function getIterator()
	{
		return new \ArrayIterator($this->_properties);
	}

	public function toArray()
	{
		$array = array();

		foreach($this->_properties as $property)
			$array[$property->name] = $property->callGet($this->_object);

		return $array;
	}
}
