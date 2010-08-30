<?php

namespace Pheasant\Tests\Relationships;

use \Pheasant\DomainObject;
use \Pheasant\Mapper\RowMapper;
use \Pheasant\Types;
use \Pheasant\Relationships;

require_once('autorun.php');
require_once(__DIR__.'/base.php');

class Hero extends DomainObject
{
	public static function initialize($builder, $pheasant)
	{
		$pheasant
			->register(__CLASS__, new RowMapper('hero'));

		$builder
			->properties(array(
				'heroid' => new Types\Sequence(),
				'alias' => new Types\String(),
				'realname' => new Types\String()
				))
			->relationships(array(
				'Powers' => new Relationships\HasMany(Power::className(),'heroid')
				));
	}
}

class Power extends DomainObject
{
	public static function initialize($builder, $pheasant)
	{
		$pheasant
			->register(__CLASS__, new RowMapper('power'));

		$builder
			->properties(array(
				'powerid' => new Types\Sequence(),
				'description' => new Types\String(),
				'heroid' => new Types\Integer()
				))
			->relationships(array(
				'Hero' => new Relationships\BelongsTo(Hero::className(), 'heroid')
				));
	}
}

class RelationshipsTestCase extends \Pheasant\Tests\MysqlTestCase
{
	public function setUp()
	{
		$migrator = new \Pheasant\Migrate\Migrator();
		$migrator
			->create('hero', Hero::schema())
			->create('power', Power::schema())
			;
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
