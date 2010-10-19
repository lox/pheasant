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
				'identityid' => new Types\Integer(),
				))
			->hasMany(array(
				'Powers' => array(Power::className(),'heroid')
				))
			->hasOne(array(
				'SecretIdentity' => array(Power::className(),'identityid'),
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
			->belongsTo(array(
				'Hero' => array(Hero::className(), 'heroid')
				));
	}
}

class SecretIdentity extends DomainObject
{
	public static function initialize($builder, $pheasant)
	{
		$pheasant
			->register(__CLASS__, new RowMapper('secretidentity'));

		$builder
			->properties(array(
				'identityid' => new Types\Sequence(),
				'realname' => new Types\String(),
				))
			->hasOne(array(
				'Hero' => array(Hero::className(), 'identityid')
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
			->create('secretidentity', SecretIdentity::schema())
			;
	}

	/*

	public function testOneToManyViaPropertySetting()
	{
		$hero = new Hero(array('alias'=>'Spider Man'));
		$hero->save();
		$this->assertEqual(count($hero->Powers), 0);

		// save via property access
		$power = new Power(array('description'=>'Spider Senses'));
		$power->heroid = $hero->heroid;
		$power->save();
		$this->assertEqual(count($hero->Powers), 1);
		$this->assertTrue($hero->Powers[0]->equals($power));
	}

	public function testOneToManyViaArrayAccess()
	{
		$hero = new Hero(array('alias'=>'Spider Man'));
		$hero->save();
		$this->assertEqual(count($hero->Powers), 0);

		// save via adding
		$power = new Power(array('description'=>'Super-human Strength'));
		$hero->Powers[] = $power;
		$power->save();
		$this->assertEqual(count($hero->Powers), 1);
		$this->assertEqual($power->heroid, 1);
		$this->assertTrue($hero->Powers[0]->equals($power));
	}

	*/

	public function testHasOneRelationship()
	{
		$this->connection()->debug = true;

		$hero = new Hero(array('alias'=>'Spider Man'));
		$hero->save();

		$identity = new SecretIdentity(array('realname'=>'Peter Parker'));
		$identity->Hero = $hero;
		$identity->save();

		//$this->assertEqual(count($hero->Powers), 1);
		//$this->assertTrue($identity->equals($power->Hero));

		//var_dump($hero);
	}

	/*

	public function testFuturesResolvedInMapping()
	{
		$identity = new SecretIdentity(array('realname'=>'Peter Parker'));
		$hero = new Hero(array('alias'=>'Spider Man'));

		// set the identityid before it's been saved, still null
		$hero->identityid = $identity->identityid;

		$identity->save();
		$hero->save();

		$this->assertEqual($identity->identityid, 1);
		$this->assertEqual($hero->identityid, 1);
	}

	*/
}
