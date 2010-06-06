<?php

namespace pheasant;
use \pheasant\mapper\TableMapper;

class PheasantInstance
{
	private $_connectionManager;
	private $_mappers=array();
	private $_finders=array();

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
		$class = is_object($class) ? get_class($class) : $object;

		if(!isset($this->_mappers[$class]))
			return $this->_mappers[$class] = new TableMapper();

		return $this->_mappers[$class];
	}

	public function finder($object)
	{
	}
}
