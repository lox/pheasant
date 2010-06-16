<?php

namespace pheasant\tests\relationships;

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

class Group extends DomainObject
{
	public static function configure($schema, $props, $rels)
	{
		$schema
			->table('group');

		$props
			->sequence('groupid')
			->string('name');
	}
}

class RelationshipsTestCase extends \pheasant\tests\MysqlTestCase
{
	public function setUp()
	{
		$migrator = new \pheasant\migrate\Migrator();
		$migrator->create(User::schema(), Group::schema());

		// create some users
		$this->users = User::import(array(
			array('firstname'=>'Frank','lastname'=>'Castle'),
			array('firstname'=>'Cletus','lastname'=>'Kasady')
			));

		//
	}

	public function testRelationshipQuery()
	{
	}
}
