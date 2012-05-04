<?php

namespace Pheasant\Tests\Finding;

use \Pheasant\Types;
use \Pheasant\DomainObject;
use \Pheasant\Query\Criteria;
use \Pheasant;

require_once(__DIR__.'/../vendor/simpletest/autorun.php');
require_once(__DIR__.'/base.php');

class User extends DomainObject
{
	public function properties()
	{
		return array(
			'userid' => new Types\Sequence(),
			'firstname' => new Types\String(),
			'lastname' => new Types\String(),
			);
	}

	public function relationships()
	{
		return array(
			'UserPrefs' => UserPref::hasMany('userid'),
			);
	}
}

class UserPref extends DomainObject
{
	public function properties()
	{
		return array(
			'userid' => new Types\Integer(),
			'pref' => new Types\String(),
			'value' => new Types\String(),
			);
	}

	public function relationships()
	{
		return array(
			'User' => User::belongsTo('userid')
			);
	}
}

class FindingTestCase extends \Pheasant\Tests\MysqlTestCase
{
	public function setUp()
	{
		$migrator = new \Pheasant\Migrate\Migrator();
		$migrator
			->create('user', User::schema())
			->create('userpref', UserPref::schema())
			;

		// create some users
		$this->users = User::import(array(
			array('firstname'=>'Frank','lastname'=>'Castle'),
			array('firstname'=>'Cletus','lastname'=>'Kasady')
		));

		// create some user prefs
		$this->userprefs = UserPref::import(array(
			array('userid'=>1,'pref'=>'autologin','value'=>'yes'),
			array('userid'=>2,'pref'=>'autologin','value'=>'no')
		));
	}

	public function testFindAll()
	{
		$users = User::find();
		$array = iterator_to_array($users);

		$this->assertEqual(2, $users->count());
		$this->assertEqual(2, count($array));
		$this->assertIsA($array[0], 'Pheasant\Tests\Finding\User');
		$this->assertIsA($array[1], 'Pheasant\Tests\Finding\User');
		$this->assertTrue($array[0]->equals($this->users[0]));
		$this->assertTrue($array[1]->equals($this->users[1]));
	}

	public function testAllIsAnAliasOfFind()
	{
		$users = User::all();
		$this->assertEqual(2, $users->count());
	}

	public function testFindLast()
	{
		$user = User::last();
		$this->assertEqual($user->firstname, 'Cletus');
		$this->assertEqual($user->lastname, 'Kasady');
	}

	public function testFindMany()
	{
		$users = User::find("lastname = ? and firstname = ?", 'Kasady', 'Cletus');
		$this->assertEqual(count($users), 1);
		$this->assertEqual($users[0]->firstname, 'Cletus');
		$this->assertEqual($users[0]->lastname, 'Kasady');
	}

	public function testFindOne()
	{
		$cletus = User::one('lastname = ?', 'Kasady');
		$this->assertEqual($cletus->firstname, 'Cletus');
		$this->assertEqual($cletus->lastname, 'Kasady');
	}

	public function testFindManyByCriteria()
	{
		$users = User::find(new Criteria("lastname = ?", array('Kasady')));
		$this->assertEqual(count($users), 1);
		$this->assertEqual($users[0]->firstname, 'Cletus');
		$this->assertEqual($users[0]->lastname, 'Kasady');
	}

	public function testFindManyByMagicalColumn()
	{
		$users = User::findByLastName('Kasady');
		$this->assertEqual(count($users), 1);
		$this->assertEqual($users[0]->firstname, 'Cletus');
		$this->assertEqual($users[0]->lastname, 'Kasady');
	}

	public function testFindManyByMultipleMagicalColumns()
	{
		$users = User::findByLastNameOrFirstName('Kasady', 'Frank');
		$this->assertEqual(count($users), 2);
	}

	public function testFindById()
	{
		$cletus = User::byId(2);
		$this->assertEqual($cletus->firstname, 'Cletus');
		$this->assertEqual($cletus->lastname, 'Kasady');
	}

	public function testOneByMagicalColumn()
	{
		$cletus = User::oneByFirstName('Cletus');
		$this->assertEqual($cletus->firstname, 'Cletus');
		$this->assertEqual($cletus->lastname, 'Kasady');
	}

	public function testFindByIn()
	{
		$cletus = User::one('lastname = ?', array('Llamas','Kasady'));
		$this->assertEqual($cletus->firstname, 'Cletus');
		$this->assertEqual($cletus->lastname, 'Kasady');
	}

	// ----------------------------------
	// Test other collection methods

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

	// ------------------------------------
	// Bugs

	public function testSavedStatusAfterFind()
	{
		$users = User::find('userid = 1');

		$this->assertTrue($users[0]->isSaved());
		$this->assertEqual($users[0]->changes(), array());
	}
}
