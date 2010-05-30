<?php

namespace pheasant;

class DomainObject
{
	private $_memento;
	private $_revision;
	private $_schema;
	private $_identity;

	/**
	 * The final constructer which initializes the object. Subclasses
	 * can implement {@link constructor()} instead
	 */
	final public function __construct()
	{
		$this->_memento = new Memento();
		$this->_schema = new Schema();

		$this->configure(
			$this->_schema,
			$this->_schema->properties(),
			$this->_schema->relationships()
			);
	}

	/**
	 * Template function for configuring a domain object
	 */
	protected function configure($schema, $props, $rels)
	{
	}

	public function identity()
	{
		if(!isset($this->_identity))
			$this->_identity = new Identity($this);

		return $this->_identity;
	}

	public function schema()
	{
		return $this->_schema;
	}

	public function isSaved()
	{
	}

	public function save()
	{
	}

	/**
	 * Returns an object for accessing a particular property
	 */
	public function future($property)
	{
		return new Future($this, $property);
	}

	// ----------------------------------------
	// property manipulators

	public function get($prop, $default=null, $future=true)
	{
		if(isset($this->_memento->{$prop}))
		{
			return $this->_memento->{$prop};
		}
		else if($future && $this->identity()->hasProperty($prop))
		{
			return $this->future($prop);
		}
		else
		{
			return $default;
		}
	}

	public function set($prop, $value)
	{
		$this->_memento->{$prop} = $value;
		return $this;
	}

	public function has($prop)
	{
		return isset($this->_memento->{$prop});
	}

	// ----------------------------------------
	// object interface

	public function __get($prop)
	{
		return $this->get($prop);
	}

	public function __set($prop, $value)
	{
		$this->set($prop, $value);
		return $value;
	}

	public function __isset($prop)
	{
		return $this->has($prop);
	}
}
