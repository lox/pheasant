<?php

namespace pheasant\tests\finding;

use pheasant\DomainObject;
use pheasant\Pheasant;

require_once('autorun.php');
require_once(__DIR__.'/base.php');

class User extends DomainObject
{
	public static function configure($schema, $props, $rels)
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
		$this->users = User::import(array(
			array('firstname'=>'Frank','lastname'=>'Castle'),
			array('firstname'=>'Cletus','lastname'=>'Kasady')
			));
	}

	public function testFindAll()
	{
		$users = User::find();

		$this->assertEqual(2, $users->count());
		$this->assertEqual(2, $users->count());
	}


}
