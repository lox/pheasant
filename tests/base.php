<?php

namespace
{
	require_once('autorun.php');

	define('BASEDIR', __DIR__.'/../');
	define('LIBDIR', BASEDIR.'lib/');

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

namespace pheasant\tests
{
	\Mock::generate('\pheasant\database\mysqli\Connection','MockConnection');

	class TestCase extends \UnitTestCase
	{
	}

	class MysqlTestCase extends TestCase
	{
		public function before($method)
		{
			parent::before($method);

			\Pheasant::setup(
				'mysql://pheasant:pheasant@localhost:/pheasanttest?charset=utf8');

			// wipe sequence pool
			$this->connection()->sequencePool()
				->initialize()
				->clear()
				;
		}

		public function connection()
		{
			return \Pheasant::connection();
		}

		public function assertConnectionExists()
		{
			$this->assertTrue($this->connection());
		}

		public function assertTableExists($table)
		{
			$this->assertTrue(false);
		}

		public function assertRowCount($sql, $count)
		{
			$this->assertTrue(false);
		}
	}
}
