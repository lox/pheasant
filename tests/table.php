<?php

namespace Pheasant\Tests\Table;

use \Pheasant;
use \Pheasant\Types;

require_once(__DIR__.'/../vendor/simpletest/autorun.php');
require_once(__DIR__.'/base.php');

class TableTestCase extends \Pheasant\Tests\MysqlTestCase
{
	public function setUp()
	{
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
		$this->assertEqual(
			$this->connection()->execute("select * from user where userid=1")->row(),
			array('userid'=>1, 'firstname'=>'Llama', 'lastname'=>'Herder')
		);
	}

	public function testUpdatingATable()
	{
		$this->table->insert(array('firstname'=>'Llama', 'lastname'=>'Herder'));
		$this->assertRowCount('select * from user', 1);

		$this->table->update(array('firstname'=>'Bob'), new Pheasant\Query\Criteria('userid=?', 1));

		$this->assertEqual(
			$this->connection()->execute("select * from user where userid=1")->row(),
			array('userid'=>1, 'firstname'=>'Bob', 'lastname'=>'Herder')
		);
	}	

	public function testUpsertingATable()
	{
		$this->table->upsert(array('firstname'=>'Llama', 'lastname'=>'Herder'));
		$this->assertRowCount('select * from user', 1);

		$this->assertEqual(
			$this->connection()->execute("select * from user where userid=1")->row(),
			array('userid'=>1, 'firstname'=>'Llama', 'lastname'=>'Herder')
		);
	}		
}

