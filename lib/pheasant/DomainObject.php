<?php

namespace pheasant;

class DomainObject
{
	private static $_schema;
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
		// lazily define the schema
		if(!isset(self::$_schema))
		{
			self::$_schema = new Schema();
			static::configure(
				self::$_schema,
				self::$_schema->properties(),
				self::$_schema->relationships()
				);

			// call user-defined constructor
			call_user_func_array(array($this,'construct'),
				func_get_args());
		}
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
	}

	public function identity()
	{
		if(!isset($this->_identity))
			$this->_identity = $this->schema()->identity($this);

		return $this->_identity;
	}

	public function schema()
	{
		return self::$_schema;
	}

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
	 * Returns an array of columns that have changed since the last save
	 * @return array
	 */
	public function changes()
	{
		return array_unique($this->_changed);
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
