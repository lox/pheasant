<?php

namespace Pheasant\Tests\TableCriteria;

use \Pheasant;
use \Pheasant\Types;

require_once(__DIR__.'/../vendor/simpletest/autorun.php');
require_once(__DIR__.'/base.php');

class TableCriteriaTestCase extends \Pheasant\Tests\MysqlTestCase
{
	public function setUp()
	{
		$this->table = $this->table('user', array(
			'userid'=>new Types\Integer(8, 'primary auto_increment'),
			'firstname'=>new Types\String(),
			'lastname'=>new Types\String(),
		));

		$this->table->insert(array('firstname'=>'Llama', 'lastname'=>'Herder'));
		$this->table->insert(array('firstname'=>'Action', 'lastname'=>'Hero'));
		$this->assertRowCount('select * from user', 2);
	}

	public function testWhereWithBindParameters()
	{
		$criteria = $this->table->where('firstname=?', 'Llama');
		
		$this->assertEqual((string) $criteria, "firstname='Llama'");
		$this->assertEqual($criteria->count(), 1);
	}

	public function testWhereWithArray()
	{
		$criteria = $this->table->where(array('firstname'=>'Llama'));
		$this->assertEqual($criteria->toSql(), "(firstname='Llama')");
		$this->assertEqual($criteria->count(), 1);
	}

	public function testWhereWithCriteria()
	{
		$criteria = $this->table->where(new \Pheasant\Query\Criteria(array('firstname'=>'Llama')));
		$this->assertEqual($criteria->toSql(), "(firstname='Llama')");
		$this->assertEqual($criteria->count(), 1);
	}	

	public function testUpdateByCriteria()
	{
		$criteria = $this->table->where('firstname=?', 'Llama')->update(array('firstname'=>'Alpaca'));
		$this->assertRowCount("select * from user where firstname='Alpaca'", 1);
	}

	public function testDeleteByCriteria()
	{
		$criteria = $this->table->where('firstname=?', 'Llama')->delete();
		$this->assertRowCount("select * from user where firstname='Llamas'", 0);
	}
}


