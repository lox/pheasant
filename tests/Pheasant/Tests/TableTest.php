<?php

namespace Pheasant\Tests;

use \Pheasant;
use \Pheasant\Types;
use \Pheasant\Query\Criteria;

class TableTest extends \Pheasant\Tests\MysqlTestCase
{
	public function setUp()
	{
		parent::setUp();

		$this->table = $this->table('user', array(
			'userid'=>new Types\Integer(8, 'primary auto_increment'),
			'firstname'=>new Types\String(),
			'lastname'=>new Types\String(),
		));

		$this->assertRowCount('select * from user', 0);
	}

	public function testInsertingIntoATable()
	{
		$this->table->insert(array('firstname'=>'Llama', 'lastname'=>'Herder'));
		$this->assertRowCount('select * from user', 1);
		$this->assertEquals(
			$this->connection()->execute("select * from user where userid=1")->row(),
			array('userid'=>1, 'firstname'=>'Llama', 'lastname'=>'Herder')
		);
	}

	public function testUpdatingATable()
	{
		$this->table->insert(array('firstname'=>'Llama', 'lastname'=>'Herder'));
		$this->assertRowCount('select * from user', 1);

		$this->table->update(array('firstname'=>'Bob'), new Pheasant\Query\Criteria('userid=?', 1));

		$this->assertEquals(
			$this->connection()->execute("select * from user where userid=1")->row(),
			array('userid'=>1, 'firstname'=>'Bob', 'lastname'=>'Herder')
		);
	}

	public function testUpsertingATable()
	{
		$this->table->upsert(array('firstname'=>'Llama', 'lastname'=>'Herder'));
		$this->assertRowCount('select * from user', 1);

		$this->assertEquals(
			$this->connection()->execute("select * from user where userid=1")->row(),
			array('userid'=>1, 'firstname'=>'Llama', 'lastname'=>'Herder')
		);
	}

	public function testFullyQualifiedTableExists()
	{
		$table = $this->connection()->table('pheasanttest.user');
		$this->assertTrue($table->exists());

		$table = $this->connection()->table('pheasanttest.never_created_table');
		$this->assertFalse($table->exists());
	}

	public function testFullyQualifiedTableInsertUpdate()
	{
		$table = $this->connection()->table('pheasanttest.user');
		$this->assertTrue($table->exists());

		$table->insert(array('firstname'=>'Llama', 'lastname'=>'Herder'));
		$this->assertRowCount('select * from user', 1);

		$table->update(array('firstname'=>'Bob'), new Pheasant\Query\Criteria('userid=?', 1));

		$this->assertEquals(
			$this->connection()->execute("select * from user where userid=1")->row(),
			array('userid'=>1, 'firstname'=>'Bob', 'lastname'=>'Herder')
		);
	}

	public function testColumnsMapToMysqlTypes()
	{
		$columns = $this->table->columns();
		$this->assertEquals(count($columns), 3);
		$this->assertEquals($columns['userid']['Type'], 'int(8)');
		$this->assertEquals($columns['firstname']['Type'], 'varchar(255)');
		$this->assertEquals($columns['lastname']['Type'], 'varchar(255)');
	}

	public function testDeletingARow()
	{
		$this->table->insert(array('firstname'=>'Llama', 'lastname'=>'Herder'));
		$this->table->insert(array('firstname'=>'Frank', 'lastname'=>'Farmer'));
		$this->assertRowCount('select * from user', 2);

		$this->table->delete(new Criteria('firstname like ?', 'Llama'));
		$this->assertRowCount('select * from user', 1);

		$this->assertEquals(
			iterator_to_array($this->connection()->execute("select firstname from user")->column()),
			array('Frank')
		);
	}

	public function testReplacingARow()
	{
		$this->table->insert(array('firstname'=>'Llama', 'lastname'=>'Herder'));
		$this->table->insert(array('firstname'=>'Frank', 'lastname'=>'Farmer'));
		$this->table->replace(array('userid'=>1, 'firstname'=>'Alpaca', 'lastname'=>'Collector'));

		$this->assertRowCount('select * from user', 2);
		$this->assertEquals(
			iterator_to_array($this->connection()->execute("select firstname from user")->column()),
			array('Alpaca', 'Frank')
		);
	}

	public function testReplacingWithoutPkeyInserts()
	{
		$this->table->insert(array('firstname'=>'Llama', 'lastname'=>'Herder'));
		$this->table->replace(array('firstname'=>'Alpaca', 'lastname'=>'Collector'));

		$this->assertRowCount('select * from user', 2);
		$this->assertEquals(
			iterator_to_array($this->connection()->execute("select firstname from user")->column()),
			array('Llama', 'Alpaca')
		);

	}
}

