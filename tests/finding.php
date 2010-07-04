<?php

namespace pheasant\tests\finding;

use pheasant\DomainObject;
use \Pheasant;

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
		$array = iterator_to_array($users);

		$this->assertEqual(2, $users->count());
		$this->assertEqual(2, count($array));

		$this->assertIsA($array[0], 'pheasant\tests\finding\User');
		$this->assertIsA($array[1], 'pheasant\tests\finding\User');
		$this->assertTrue($array[0]->equals($this->users[0]));
		$this->assertTrue($array[1]->equals($this->users[1]));
	}

	public function testFindByProperty()
	{
		$users = User::find("lastname = ?", 'Kasady');

		$this->assertEqual(count($users), 1);
		$this->assertEqual($users[0]->firstname, 'Cletus');
		$this->assertEqual($users[0]->lastname, 'Kasady');
	}

	public function testFilter()
	{
		User::import(array(
			array('firstname'=>'Frank','lastname'=>'Beechworth'),
			));

		$users = User::find()
			->filter("firstname like ?", 'Fra%')
			->filter("lastname in (?)", 'Castle')
			;

		$this->assertEqual(count($users), 1);
		$this->assertEqual($users[0]->firstname, 'Frank');
		$this->assertEqual($users[0]->lastname, 'Castle');
	}

	public function testFilterViaInvoke()
	{
		$users = User::find();
		$filtered = $users("firstname = ?", 'Frank');

		$this->assertEqual(count($filtered), 1);
		$this->assertEqual($filtered[0]->firstname, 'Frank');
		$this->assertEqual($filtered[0]->lastname, 'Castle');
	}

	public function testSavedStatusAfterFind()
	{
		$users = User::find('userid = 1');

		$this->assertTrue($users[0]->isSaved());
		$this->assertEqual($users[0]->changes(), array());
	}
}
