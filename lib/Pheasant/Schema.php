<?php

namespace Pheasant;

/**
 * A schema describes what a DomainObject can contain and how to access it's attributes.
 */
class Schema
{
	private
		$_class,
		$_all=array(),
		$_props=array(),
		$_rels=array(),
		$_getters=array(),
		$_setters=array();

	/**
	 * Constructor
	 */
	public function __construct($class, $props, $rels, $getters, $setters)
	{
		$this->_class = $class;
		$this->_props = $props;
		$this->_rels = $rels;
		$this->_getters = $getters;
		$this->_setters = $setters;
	}

	/**
	 * Returns an identity for a domain object
	 * @return Identity
	 */
	public function identity($object)
	{
		$properties = array_filter($this->_props, function($property) {
			return $property->type->options->primary;
		});

		return new Identity($properties, $object);
	}

	/**
	 * Returns an array with property defaults
	 * @return array
	 */
	public function defaults()
	{
		$defaults = array();

		foreach($this->_props as $key=>$prop)
			$defaults[$key] = $prop->defaultValue();

		return $defaults;
	}

	/**
	 * Returns the Property objects for the schema
	 * @return array
	 */
	public function properties()
	{
		return $this->_props;
	}

	/**
	 * Returns the Relationship objects for the schema
	 * @return array
	 */
	public function relationships()
	{
		return $this->_rels;
	}

	/**
	 * Hydrates an array into the domain object of the schema
	 * @return object
	 */
	public function hydrate($row, $saved=true)
	{
		$class = $this->_class;
		return $class::fromArray($row, $saved);
	}

	// ------------------------------------
	// route primitives to properties and relationships

	/**
	 * Return a closure for getting an attribute from a domain object
	 * @return closure
	 */
	public function getter($attr)
	{
		var_dump("getter for $attr on {$this->_class}");

		if(isset($this->_getters[$attr]))
			return $this->_getters[$attr];

		else if(isset($this->_props[$attr]))
			return $this->_props[$attr]->getter($attr);

		else if(isset($this->_rels[$attr]))
			return $this->_rels[$attr]->getter($attr);

		throw new Exception("No getter available for $attr");
	}

	/**
	 * Return a closure for setting an attribute on a domain object
	 * @return closure
	 */
	public function setter($attr)
	{
		var_dump("setter for $attr on {$this->_class}");
		var_dump($this->_rels[$attr]);

		if(isset($this->_setters[$attr]))
			return $this->_setters[$attr];

		else if(isset($this->_props[$attr]))
			return $this->_props[$attr]->setter($attr);

		else if(isset($this->_rels[$attr]))
			return $this->_rels[$attr]->setter($attr);

		throw new Exception("No setter available for $attr");
	}
}
