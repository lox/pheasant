<?php

namespace Pheasant;

/**
 * Builder for an {@link Schema}
 */
class SchemaBuilder
{
	private $_properties=array(), $_relationships=array();

	/**
	 * Sets the schema properties
	 * chainable
	 */
	public function properties($map)
	{
		$this->_properties = array();

		foreach($map as $name=>$type)
			$this->_properties[$name] = new Property($name, $type);

		return $this;
	}

	/**
	 * Sets the schema relationships
	 * @chainable
	 */
	public function relationships($map)
	{
		$this->_relationships = array();

		foreach($map as $name=>$type)
			$this->_relationships[$name] = new Relationship($name, $type);

		return $this;
	}

	/**
	 * Builds a schema object
	 */
	public function build($class)
	{
		if(!isset($this->_properties))
			throw new Exception("A schema must have properties");

		return new Schema($class, $this->_properties, $this->_relationships);
	}
}
