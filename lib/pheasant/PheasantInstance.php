<?php

namespace pheasant;

class PheasantInstance
{
	private $_connectionManager;
	private $_mappers=array();
	private $_schemas=array();

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

	public function schema($class)
	{
		$class = is_object($class) ? get_class($class) : $class;

		if(!isset($this->_schemas[$class]))
			return $this->_schemas[$class] = new \pheasant\Schema();

		return $this->_schemas[$class];
	}
}
