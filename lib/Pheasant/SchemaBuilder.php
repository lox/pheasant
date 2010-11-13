<?php

namespace Pheasant;

/**
 * Builder for an {@link Schema}
 */
class SchemaBuilder
{
	private
		$_properties=array(),
        $_relationships=array(),
        $_events=array(),
        $_getters=array(),
        $_setters=array()
        ;

	/**
	 * Sets the schema properties
	 * chainable
	 */
	public function properties($map)
	{
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
		foreach($map as $name=>$type)
			$this->_relationships[$name] = new Relationship($name, $type);

		return $this;
    }

	/**
	 * Sets the schema events
	 * @chainable
	 */
	public function events($map)
	{
		foreach($map as $name=>$callback)
			$this->_events[$name] = $callback;

		return $this;
    }

    public function getters($map)
    {
        foreach($map as $name=>$callback)
			$this->_getters[$name] = $callback;

		return $this;
    }

    public function setters($map)
    {
        foreach($map as $name=>$callback)
			$this->_setters[$name] = $callback;

		return $this;
    }

	/**
	 * Adds a collection of {@link HasOne} relationships
	 * @chainable
	 */
	public function hasOne($map)
	{
		return $this->_addRelationships($map,
			'\Pheasant\Relationships\HasOne');
	}

	/**
	 * Adds a collection of {@link HasMany} relationships
	 * @chainable
	 */
	public function hasMany($map)
	{
		return $this->_addRelationships($map,
			'\Pheasant\Relationships\HasMany');
	}

	/**
	 * Adds a collection of {@link BelongsTo} relationships
	 * @chainable
	 */
	public function belongsTo($map)
	{
		return $this->_addRelationships($map,
			'\Pheasant\Relationships\BelongsTo');
	}

	/**
	 * Adds a collection of relationships
	 * @chainable
	 */
	public function _addRelationships($map, $relClass)
	{
		foreach($map as $name=>$array)
		{
			$class = new \ReflectionClass($relClass);
			$this->_relationships[$name] = new Relationship($name,
				$class->newInstanceArgs($array));
		}

		return $this;
	}

	/**
	 * Builds a schema object
	 */
	public function build($class)
	{
		if(!isset($this->_properties))
			throw new Exception("A schema must have properties");

		return new Schema($class, array(
			'properties' => $this->_properties,
			'relationships' => $this->_relationships,
			'getters' => $this->_getters,
			'setters' => $this->_setters,
			'events' => $this->_events
			));
	}
}
