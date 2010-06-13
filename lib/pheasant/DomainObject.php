<?php

namespace pheasant;

/**
 * An object which represents an entity in the problem domain.
 */
class DomainObject
{
	private $_identity;
	private $_data = array();
	private $_changed = array();
	private $_saved=false;

	/**
	 * The final constructer which initializes the object. Subclasses
	 * can implement {@link constructor()} instead
	 */
	final public function __construct()
	{
		// configure the schema if needed
		if(!Pheasant::isConfigured($this))
			Pheasant::configure(get_class($this));

		// call user-defined constructor
		call_user_func_array(array($this,'construct'),
			func_get_args());
	}

	/**
	 * Template function for configuring a domain object. Called once per type
	 * of domain object
	 */
	protected static function configure($schema, $props, $rels)
	{
	}

	/**
	 * Template function for constructing a domain object instance, called on
	 * each object construction
	 */
	protected function construct()
	{
		foreach(func_get_args() as $arg)
			if(is_array($arg)) $this->load($arg);
	}

	/**
	 * Returns an Identity object for the domain object
	 * @return Identity
	 */
	public function identity()
	{
		if(!isset($this->_identity))
			$this->_identity = $this->schema()->identity($this);

		return $this->_identity;
	}

	/**
	 * Returns the Schema registered for this class
	 * @return Schema
	*/
	public function schema()
	{
		return Pheasant::schema($this);
	}

	/**
	 * Returns whether the object has been saved
	 * @return bool
	 */
	public function isSaved()
	{
		return $this->_saved;
	}

	/**
	 * Saves the domain object via the associated mapper
	 * @chainable
	 */
	public function save()
	{
		$mapper = Pheasant::mapper($this);
		$mapper->save($this);
		$this->_saved = true;
		$this->_changed = array();
		return $this;
	}

	/**
	 * Returns a key=>val array of properties that have changed since the last save
	 * @return array
	 */
	public function changes()
	{
		$changes = array();
		foreach(array_unique($this->_changed) as $key)
			$changes[$key] = $this->get($key, false);

		return $changes;
	}

	/**
	 * Returns an object for accessing a particular property
	 * @return Future
	 */
	public function future($property)
	{
		return new Future($this, $property);
	}

	// ----------------------------------------
	// static helpers

	/**
	 * Creates an instance from an array, bypassing the constructor
	 */
	public static function fromArray($array)
	{
		$className = get_called_class();

		// hack that uses object deserialization to bypass constructor
		$object = unserialize(sprintf('O:%d:"%s":0:{}',
			strlen($className),
			$className));

		return $object->load($array);
	}

	/**
	 * Gets the registered mapper for the domain object
	 */
	public static function find($sql=null, $params=array())
	{
		return static::mapper()->find($sql, $params);
	}

	/**
	 * Gets the registered mapper for the domain object
	 */
	public static function mapper()
	{
		return Pheasant::mapper(get_called_class());
	}

	/**
	 * Creates and saves a array or arrays as domain objects
	 * @return array of saved domain objects
	 */
	public static function import($records)
	{
		$objects = array();

		foreach($records as $record)
		{
			$object = static::mapper()->hydrate($record);
			$object->save();
			$objects []= $object;
		}

		return $objects;
	}

	// ----------------------------------------
	// property manipulators

	/**
	 * Gets the value of a property, optionally as a Future if the value
	 * doesn't exist yet
	 */
	public function get($prop, $future=false, $default=null)
	{
		if(isset($this->_data[$prop]))
		{
			return $this->_data[$prop];
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

	/**
	 * Sets the value of a property
	 */
	public function set($prop, $value)
	{
		$this->_data[$prop] = $value;
		$this->_changed[] = $prop;
	}

	public function has($prop)
	{
		return isset($this->_data[$prop]);
	}

	/**
	 * Loads an array of values into the objecty
	 * @chainable
	 */
	public function load($array)
	{
		foreach($array as $key=>$value)
			$this->set($key, $value);

		return $this;
	}

	// ----------------------------------------
	// object interface

	public function __get($prop)
	{
		return $this->get($prop, true);
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
