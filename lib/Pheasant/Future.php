<?php

namespace Pheasant;

/**
 * A handle to a property in a particular object, for future dereferencing
 */
class Future
{
	private
		$_property,
		$object;

	/**
	 * Constructor
	 */
	public function __construct($property, $object)
	{
		$this->_property = $property;
		$this->_object = $object;
	}

	/**
	 * Returns the value
	 */
	public function value()
	{
		return $this->_object->get($this->_property->name);
	}

	/**
	 * Returns a string version of {@link value()}
	 */
	public function __toString()
	{
		return (string) $this->value();
	}
}
