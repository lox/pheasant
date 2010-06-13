<?php

namespace pheasant;

class PheasantInstance
{
	private $_connectionManager;
	private $_mappers=array();
	private $_schemas=array();

	public function connectionManager()
	{
		if(!isset($this->_connectionManager))
		{
			$this->_connectionManager = new database\ConnectionManager();
		}

		return $this->_connectionManager;
	}

	public function connection($name='default')
	{
		return $this->connectionManager()->connection($name);
	}

	/**
	 * Returns the mapper registered for an object, defaults to a TableMapper
	 * @param mixed either a classname or an object
	 * @return Mapper
	 */
	public function mapper($class)
	{
		$class = is_object($class) ? get_class($class) : $class;

		if(!isset($this->_mappers[$class]))
			return $this->_mappers[$class] = new \pheasant\mapper\TableMapper($class);

		return $this->_mappers[$class];
	}

	/**
	 * Determines if a class has had it's schema configured
	 * @return bool
	 */
	public function isConfigured($class)
	{
		$class = is_object($class) ? get_class($class) : $class;

		return isset($this->_schemas[$class]);
	}

	/**
	 * Creates a new schema and configures it via Class::configure()
	 * @param string the full qualified class name
	 * @return Schema
	 */
	public function configure($class)
	{
		$schema = new \pheasant\Schema();
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
		$class = is_object($class) ? get_class($class) : $class;

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
