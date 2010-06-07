<?php

namespace pheasant\tests\query;

use pheasant\Pheasant;
use pheasant\query\QueryBuilder;
use pheasant\query\Query;

require_once('autorun.php');
require_once(__DIR__.'/base.php');

class QueryTestCase extends \pheasant\tests\MysqlTestCase
{
	public function setUp()
	{
		$table = Pheasant::connection()->table('user');
		$table
			->integer('userid', 8, array('primary', 'auto_increment'))
			->string('firstname')
			->string('lastname')
			->create()
			;

		// create some users
		$table->insert(array('userid'=>null,'firstname'=>'Frank','lastname'=>'Castle'));
		$table->insert(array('userid'=>null,'firstname'=>'Cletus','lastname'=>'Kasady'));
	}

	public function testQuerying()
	{
		$query = new Query();
		$query
			->select('firstname')
			->from('user')
			->where('lastname=?','Castle')
			;

		$this->assertEqual(1, $query->count());
		$this->assertEqual(1, $query->execute()->count());
		$this->assertEqual(array('firstname'=>'Frank'), $query->execute()->offsetGet(0));
	}
}

