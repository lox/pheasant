<?php

namespace pheasant;

class DomainObject
{
	private $_memento;
	private $_identity;

	/**
	 * The final constructer which initializes the object. Subclasses
	 * can implement {@link constructor()} instead
	 */
	final public function __construct()
	{
		$this->_memento = new Memento();
		Pheasant::construct($this, func_get_args());
	}

	/**
	 * Template function for configuring a domain object. Called once per type
	 * of domain object
	 */
	private function configure($schema, $props, $rels)
	{
	}

	/**
	 * Template function for constructing a domain object instance, called on
	 * each object construction
	 */
	private function construct()
	{
	}

	public function identity()
	{
		if(!isset($this->_identity))
			$this->_identity = $this->schema()->identity($this);

		return $this->_identity;
	}

	public function schema()
	{
		return Pheasant::schema($this);
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
		else if(isset($this->schema()->properties()->{$prop}))
		{
			return $future ? $this->future($prop) : $default;
		}
		else
		{
			throw new Exception("Unknown property $prop");
		}
	}

	public function set($prop, $value)
	{
		$this->_memento->{$prop} = $value;
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
