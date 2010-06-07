<?php

namespace pheasant\tests\finding;

use pheasant\DomainObject;
use pheasant\Pheasant;

require_once('autorun.php');
require_once(__DIR__.'/base.php');

class User extends DomainObject
{
	protected static function configure($schema, $props, $rels)
	{
		$schema
			->table('user');

		$props
			->sequence('userid')
			->string('firstname')
			->string('lastname');
	}
}

class BasicFindingTestCase extends \pheasant\tests\MysqlTestCase
{
	public function setUp()
	{
		$table = Pheasant::connection()->table('user');
		$table
			->integer('userid', 8, array('primary'))
			->string('firstname')
			->string('lastname')
			->create()
			;

		// create some users
		$user1 = new User(array('firstname'=>'Frank','lastname'=>'Castle'));
		$user1->save();
		$user2 = new User(array('firstname'=>'Cletus','lastname'=>'Kasady'));
		$user2->save();
	}

	public function testBasicFinding()
	{
		// test via mapper first
		$mapper = User::mapper();
		$users = $mapper->find();

		$this->assertEqual(2, $users->count());
	}
}
