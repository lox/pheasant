<?php

namespace Pheasant\Tests;

class MysqlTestCase extends \PHPUnit_Framework_TestCase
{
	public function setUp()
	{
		// initialize a new pheasant
		$this->pheasant = \Pheasant::setup(
			'mysql://root@localhost/pheasanttest?charset=utf8'
			);

		// wipe sequence pool
		$this->pheasant->connection()
			->sequencePool()
			->initialize()
			->clear()
			;
	}

	public function tearDown()
	{
		$this->pheasant->connection()->close();
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

	public function assertRowCount($count, $sql)
	{
		if(is_object($sql))
			$sql = $sql->toSql();

		$result = $this->connection()->execute($sql);
		$this->assertEquals($result->count(), $count);
	}
}
