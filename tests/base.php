<?php

namespace
{
	define('BASEDIR', __DIR__.'/../');
	define('LIBDIR', BASEDIR.'lib/');

	// show all errors
	error_reporting(E_ALL);

	// set up autoload
	function __autoload($className)
	{
		if(!class_exists($className))
		{
			$path = LIBDIR . str_replace('\\','/',$className).'.php';

			if(file_exists($path))
				require_once($path);

			if(!class_exists($className) && !interface_exists($className))
				return false;
		}
	}
}

namespace Pheasant\Tests
{
	\Mock::generate('\Pheasant\Database\Mysqli\Connection','MockConnection');

	class DbTestCase extends \UnitTestCase
	{
		public function before($method)
		{
			parent::before($method);

			switch(strtolower(getenv('PHEASANT_DRIVER')))
			{
				case 'mysql': 
					$dsn = 'mysql://pheasant:pheasant@localhost/pheasanttest?charset=utf8';
					break;
				case 'sqlite':
					$dsn = 'sqlite://memory';
					break;
				default:
					throw new \InvalidArgumentException("Unsupported driver ".getenv('PHEASANT_DRIVER'));	
			}

			// initialize a new pheasant
			$this->pheasant = \Pheasant::setup($dsn);

			// wipe sequence pool
			$this->pheasant->connection()
				->sequencePool()
				->initialize()
				->clear()
				;
		}

		// Helper to return a connection
		public function connection()
		{
			return $this->pheasant->connection();
		}

		// Helper to drop and re-create a table
		public function table($name, $columns)
		{
			$table = $this->pheasant->connection()->table($name);

			if($table->exists()) $table->drop();

			$table->create($columns);

			$this->assertTableExists($name);

			return $table;
		}

		public function assertConnectionExists()
		{
			$this->assertTrue($this->pheasant->connection());
		}

		public function assertTableExists($table)
		{
			$this->assertTrue($this->pheasant->connection()->table($table)->exists());
		}

		public function assertRowCount($sql, $count)
		{
			$result = $this->connection()->execute($sql);
			$this->assertEqual($result->count(), $count);
		}
	}
}
