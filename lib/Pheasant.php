<?php

// manually include type functions
require_once(dirname(__FILE__).'/Pheasant/Types.php');

/**
 * Central object for object mapping and lookups, an instance is stored statically
 * in each domain object class
 */
class Pheasant
{
	private $_connections;

	/**
	 * Constructor
	 * @param $dsn string a database dsn
	 */
	public function __construct($dsn=null)
	{
		$this->_connections = new \Pheasant\Database\ConnectionManager();

		// the provided dsn is a default
		if($dsn) $this->_connections->addConnection('default', $dsn);
	}

	/**
	 * @return object
	 */
	public function connection($name='default')
	{
		return $this->_connections->connection($name);
	}
}
