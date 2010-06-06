<?php

namespace pheasant;

class Identity implements \IteratorAggregate
{
	private $_object;

	public function __construct(DomainObject $object)
	{
		$this->_object = $object;
	}

	public function properties()
	{
		$keys = $this->_object->schema()->properties()->primaryKeys();
		ksort($keys);
		return $keys;
	}

	public function hasProperty($property)
	{
		return in_array($property, $this->properties());
	}

	public function getIterator()
	{
		return new \ArrayIterator($this->toArray());
	}

	public function toArray()
	{
		$array = array();

		foreach($this->properties() as $property)
		{
			$array[$property->name] = $this->_object->get($property->name);
		}

		return $array;
	}
}
