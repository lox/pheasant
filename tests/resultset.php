<?php

namespace Pheasant\Tests\ResultSet;

use \Pheasant\Database\Binder;
use \Pheasant\Types;

require_once(__DIR__.'/../vendor/simpletest/autorun.php');
require_once(__DIR__.'/base.php');

class ResultSetTestCase extends \Pheasant\Tests\MysqlTestCase
{
	public function setUp()
	{
		$t = $this->table('user', array(
			'userid'=>new Types\Integer(8, 'primary auto_increment'),
			'name'=>new Types\String(),
			'value'=>new Types\Integer(),
			'active'=>new Types\Boolean(),
		));

		$t->insert(array('name'=>'Llama', 'value'=>24, 'active'=>false));
		$t->insert(array('name'=>'Drama', 'value'=>NULL, 'active'=>true));
	}

	public function testGettingARow()
	{
		$rs = $this->connection()->execute('SELECT name,value,active FROM user');

		$this->assertEqual($rs->count(), 2);
		$this->assertEqual($rs->row(), array('name'=>'Llama', 'value'=>24, 'active'=>false));
		$this->assertEqual($rs->row(), array('name'=>'Drama', 'value'=>NULL, 'active'=>true));
		$this->assertSame($rs->row(), NULL);

		// this should rewind and seek
		$this->assertEqual($rs->seek(1)->row(), array('name'=>'Drama', 'value'=>NULL, 'active'=>true));

	}

	public function testGettingAScalar()
	{
		$rs = $this->connection()->execute('SELECT name,value,active FROM user');

		$this->assertEqual($rs->scalar(), 'Llama');
		$this->assertEqual($rs->scalar(), 'Drama');
		$this->assertSame($rs->scalar(), null);

		// this should rewind and seek
		$this->assertEqual($rs->seek(0)->scalar(1), 24);
		$this->assertEqual($rs->seek(0)->scalar('name'), 'Llama');
	}

	public function testGettingAColumn()
	{
		$rs = $this->connection()->execute('SELECT name,value,active FROM user');

		$this->assertEqual(iterator_to_array($rs->column()), array('Llama', 'Drama'));
	}

	public function testGettingFieldsWithResults()
	{
		$rs = $this->connection()->execute('SELECT name,value,active FROM user');
		$fields = $rs->fields();

		$this->assertEqual(count($fields), 3);
		$this->assertEqual($fields[0]->name, 'name');
		$this->assertEqual($fields[1]->name, 'value');
		$this->assertEqual($fields[2]->name, 'active');
	}

	public function testGettingFieldsWithNoResults()
	{
		$rs = $this->connection()->execute('SELECT name,value,active FROM user WHERE 1=0');
		$fields = $rs->fields();

		$this->assertEqual(count($rs), 0);
		$this->assertEqual(count($fields), 3);
		$this->assertEqual($fields[0]->name, 'name');
		$this->assertEqual($fields[1]->name, 'value');
		$this->assertEqual($fields[2]->name, 'active');
	}

}

