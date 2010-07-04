<?php

namespace pheasant\tests\relationships;

use pheasant\DomainObject;
use pheasant\Pheasant;

require_once('autorun.php');
require_once(__DIR__.'/base.php');

class Hero extends DomainObject
{
	public static function configure($schema, $props, $rels)
	{
		$schema
			->table('hero');

		$props
			->sequence('heroid')
			->string('alias')
			->string('realname');

		$rels
			->hasMany('Powers', Power::mapper(), 'heroid');
	}
}

class Power extends DomainObject
{
	public static function configure($schema, $props, $rels)
	{
		$schema
			->table('power');

		$props
			->sequence('powerid')
			->string('description')
			->integer('heroid');

		$rels
			->belongsTo('Hero', Hero::mapper(), 'heroid');
	}
}

class RelationshipsTestCase extends \pheasant\tests\MysqlTestCase
{
	public function setUp()
	{
		$migrator = new \pheasant\migrate\Migrator();
		$migrator->create(Hero::schema(), Power::schema());
	}

	public function testOneToManyRelationship()
	{
		$hero = new Hero(array('alias'=>'Spider Man','realname'=>'Peter Parker'));
		$hero->save();

		$this->assertEqual(count($hero->Powers), 0);

		$power = new Power(array('description'=>'Spider Senses'));
		$power->heroid = $hero->heroid;
		$power->save();

		$this->assertEqual(count($hero->Powers), 1);
		$this->assertTrue($hero->Powers[0]->equals($power));
	}
}
