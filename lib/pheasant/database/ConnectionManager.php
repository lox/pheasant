<?php

namespace pheasant\database;

class ConnectionManager
{
	private $_connections=array();
	private $_drivers=array();

	public function addConnection($name, $dsn)
	{
		$this->_connections[$name][] = $dsn;
	}

	public function connection($name)
	{
		if(!isset($this->_connections[$name]))
			throw new \pheasant\Exception("No connection called $name registered");

		$index = array_rand($this->_connections[$name]);
		$connection = $this->_connections[$name][$index];

		if(is_string($connection))
			$connection = $this->_connections[$name][$index] =
				$this->_buildConnection($connection);

		return $connection;
	}

	public function addDriver($name, $class)
	{
		$this->_drivers[$name] = $class;
	}

	private function _buildConnection($dsn)
	{
		$driver = substr($dsn, 0, strpos($dsn,':'));

		// look at registered drivers
		if(isset($this->_drivers[$driver]))
		{
			if(is_string($this->_drivers[$driver]))
			{
				$class = $this->_drivers[$driver];
				return new $class($dsn);
			}
			else
			{
				$callable = $this->_drivers[$driver];
				return new call_user_func($callable, $dsn);
			}
		}

		// check built in drivers
		switch($driver)
		{
			case 'mysql':
			case 'mysqli':
				return new mysqli\Connection($dsn);
		}

		throw new \pheasant\Exception("Unknown driver $driver");
	}
}
