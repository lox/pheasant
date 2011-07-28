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
				throw new Exception("Unable to load $className");
		}
	}
}

namespace Pheasant\Tests
{
	\Mock::generate('\Pheasant\Database\Mysqli\Connection','MockConnection');

	class MysqlTestCase extends \UnitTestCase
	{
		public function before($method)
		{
			parent::before($method);

			// initialize a new pheasant
			$this->pheasant = \Pheasant::setup(
				'mysql://pheasant:pheasant@localhost:/pheasanttest?charset=utf8'
				);

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
