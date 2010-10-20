<?php

namespace Pheasant;

/**
 * A handle to a property in a particular object, for future dereferencing
 */
class PropertyReference
{
	private
		$_property,
		$_object;

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

	/**
	 * Saves the internal object
	 * @chainable
	 */
	public function save()
	{
		var_dump('saving',$this);

		$this->_object->save();
		return $this;
	}
}
