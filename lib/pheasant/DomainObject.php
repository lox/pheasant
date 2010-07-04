<?php

namespace pheasant;

use \Pheasant;

/**
 * An object which represents an entity in the problem domain.
 */
class DomainObject
{
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
		return $this->schema()->identity($this);
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
	 * Change the objects saved state
	 * @chainable
	 */
	public function markSaved($value=true)
	{
		$this->_saved = $value;
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
	 * Clears the changes array
	 * @chainable
	 */
	public function clearChanges()
	{
		$this->_changed = array();
		return $this;
	}

	/**
	 * Returns an object for accessing a particular property
	 * @return Future
	 */
	public function future($property)
	{
		return new Future($this, $property);
	}

	/**
	 * Returns the object as an array
	 * @return array
	 */
	public function toArray()
	{
		return $this->_data;
	}

	// ----------------------------------------
	// static helpers

	/**
	 * Returns the Schema registered for this class
	 * @return Schema
	*/
	public static function schema()
	{
		return Pheasant::schema(get_called_class());
	}

	/**
	 * Creates an instance from an array, bypassing the constructor
	 */
	public static function fromArray($array, $saved=false)
	{
		$className = get_called_class();

		// hack that uses object deserialization to bypass constructor
		$object = unserialize(sprintf('O:%d:"%s":0:{}',
			strlen($className),
			$className));

		$object->load($array);

		// saved implies cleared changes
		if($saved)
			$object->markSaved(true)->clearChanges();

		return $object;
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
		$mapper = static::mapper();

		foreach($records as $record)
		{
			$object = $mapper->hydrate($record);
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
	 * Loads an array of values into the object, optionally marking the object saved
	 * @chainable
	 */
	public function load($array)
	{
		foreach($array as $key=>$value)
			$this->set($key, $value);

		return $this;
	}

	/**
	 * Returns the a collection of objects representing the relationship
	 */
	public function relationship($key)
	{
		$relationship = self::schema()->relationships()->$key;

		// build query
		$query = $relationship->mapper->query();
		$query->andWhere($relationship->criteria . ' = ?',
			$this->get($relationship->criteria));

		return new Collection($relationship->mapper, $query);
	}

	/**
	 * Compares the properties of one domain object to that of another
	 */
	public function equals($object)
	{
		return $this->toArray() == $object->toArray();
	}

	// ----------------------------------------
	// object interface

	private function _isRelationship($key)
	{
		return preg_match('/^[A-Z]/', $key);
	}

	public function __get($key)
	{
		return $this->_isRelationship($key)
			? $this->relationship($key)
			: $this->get($key, true);
	}

	public function __set($prop, $value)
	{
		if(preg_match('/^[A-Z]/', $prop))
		{
			$relationship = $this->relationship($prop);
			$relationship[] = $value;
			return $value;
		}
		else
		{
			$this->set($prop, $value);
			return $value;
		}
	}

	public function __isset($prop)
	{
		return $this->has($prop);
	}
}
