<?php

namespace Pheasant;

class Schema
{
	private $_class, $_all, $_properties, $_relationships;

	/**
	 * Constructor
	 */
	public function __construct($class, $properties, $relationships=array())
	{
		$this->_class = $class;
		$this->_properties = $properties;
		$this->_relationships = $relationships;
		$this->_all = array_merge($properties, $relationships);
	}

	public function identity($object)
	{
		$properties = array_filter($this->_properties, function($property) {
			return $property->options->primary;
		});

		return new Identity($properties, $object);
	}

	public function properties()
	{
		return $this->_properties;
	}

	public function hydrate($row, $saved=true)
	{
		$class = $this->_class;
		return $class::fromArray($row, $saved);
	}

	// ------------------------------------
	// route primitives to properties and relationships

	public function __get($key)
	{
		if(!isset($this->_all[$key]))
			throw new Exception("{$this->_class} schema has no attribute for '$key'");

		return $this->_all[$key];
	}

	public function __isset($key)
	{
		return isset($this->_all[$key]);
	}
}
