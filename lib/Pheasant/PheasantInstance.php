<?php

namespace Pheasant;

class PheasantInstance
{
	private $_connectionManager;
	private $_schemas=array();
	private $_mappers;
	private $_finders;

	public function __construct()
	{
		$this->_mappers = new Registry();
		$this->_finders = new Registry();
	}

	public function connectionManager()
	{
		if(!isset($this->_connectionManager))
		{
			$this->_connectionManager = new Database\ConnectionManager();
		}

		return $this->_connectionManager;
	}

	public function connection($name='default')
	{
		return $this->connectionManager()->connection($name);
	}

	/**
	 * Returns the classname for an object or a classname
	 * @return string
	 */
	private function _className($mixed)
	{
		return is_object($mixed) ? get_class($mixed) : $mixed;
	}

	public function mapper($class)
	{
		return $this->_mappers->lookup($this->_className($class));
	}

	public function setMapper($class, $mapper)
	{
		$this->_mappers->register($this->_className($class), $mapper);
		return $this;
	}

	public function setDefaultMapper($mapper)
	{
		$this->_mappers->setDefaultCallback($mapper);
		return $this;
	}

	public function finder($class)
	{
		return $this->_finders->lookup($this->_className($class));
	}

	public function setFinder($class, $finder)
	{
		$this->_finders->register($this->_className($class), $finder);
		return $this;
	}

	public function setDefaultFinder($finder)
	{
		$this->_finders->setDefaultCallback($finder);
		return $this;
	}

	/**
	 * Determines if a class has had it's schema configured
	 * @return bool
	 */
	public function isConfigured($class)
	{
		return isset($this->_schemas[$this->_className($class)]);
	}

	/**
	 * Creates a new schema and configures it via Class::configure()
	 * @param string the full qualified class name
	 * @return Schema
	 */
	public function configure($class)
	{
		$schema = new \Pheasant\Schema();
		forward_static_call(array($class,'configure'),
			$schema,
			$schema->properties(),
			$schema->relationships()
			);

		return $schema;
	}

	/**
	 * Returns a schema instance for a class or object, configures in needed
	 * @return Schema
	 */
	public function schema($class)
	{
		$class = $this->_className($class);

		if(!isset($this->_schemas[$class]))
			$this->_schemas[$class] = $this->configure($class);

		return $this->_schemas[$class];
	}

	/**
	 * Resets registered mappers and schemas
	 */
	public function reset()
	{
		$this->_mappers = array();
		$this->_schemas = array();
		return $this;
	}
}
