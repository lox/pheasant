<?php

namespace pheasant;
use \pheasant\mapper\TableMapper;

class PheasantInstance
{
	private $_connectionManager;
	private $_schema=array();
	private $_mappers=array();

	public function setup($dsn)
	{
		$this->connectionManager()
			->addConnection('default', $dsn)
			;
	}

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
		return $this->_connectionManager->connection($name);
	}

	/**
	 * Generates a closure that invokes a method regardless of whether it's private
	 * or protected. Treat with extreme caution.
	 * @return closure
	 */
	public function methodClosure($object, $method)
	{
		return function() use($object, $method) {
			$refObj = new \ReflectionObject($object);
			$refMethod = $refObj->getMethod($method);
			$refMethod->setAccessible(true);
			return $refMethod->invokeArgs($object, func_get_args());
		};
	}

	/**
	 * Returns the Schema instance for a
	 */
	public function schema($class)
	{
		$class = is_object($class) ? get_class($class) : $class;

		if(!isset($this->_schema[$class]))
			throw new Exception("No schema created for $class");

		return $this->_schema[$class];
	}

	/**
	 * Constructs a domain object, invoking DomainObject::configure() if required
	 * and also DomainObject::construct()
	 */
	public function construct($object, $params)
	{
		$class = get_class($object);

		if(!isset($this->_schema[$class]))
		{
			$schema = $this->_schema[$class] = new Schema();
			$configure = $this->methodClosure($object, 'configure');
			$configure(
				$schema,
				$schema->properties(),
				$schema->relationships()
				);
		}

		$construct = $this->methodClosure($object, 'construct');
		call_user_func_array($construct, $params);
		return $object;
	}

	/**
	 * Returns the mapper registered for an object, defaults to a TableMapper
	 * @param mixed either a classname or an object
	 * @return Mapper
	 */
	public function mapper($class)
	{
		$class = is_object($class) ? get_class($class) : $object;

		if(!isset($this->_mappers[$class]))
			return $this->_mappers[$class] = new TableMapper();

		return $this->_mappers[$class];
	}

	public function finder($object)
	{
	}
}
