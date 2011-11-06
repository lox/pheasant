<?php

namespace Pheasant\Tests\Relationships;

use \Pheasant\DomainObject;
use \Pheasant\Mapper\RowMapper;
use \Pheasant\Types;

require_once(__DIR__.'/../vendor/simpletest/autorun.php');
require_once(__DIR__.'/base.php');

class Hero extends DomainObject
{
	public function properties()
	{
		return array(
			'heroid' => new Types\Sequence(),
			'alias' => new Types\String(),
			'identityid' => new Types\Integer(),
			);
	}

	public function relationships()
	{
		return array(
			'Powers' => Power::hasMany('heroid'),
			'SecretIdentity' => SecretIdentity::belongsTo('identityid'),
			);
	}
}

class Power extends DomainObject
{
	public function properties()
	{
		return array(
			'powerid' => new Types\Sequence(),
			'description' => new Types\String(),
			'heroid' => new Types\Integer()
			);
	}

	public function relationships()
	{
		return array(
			'Hero' => Hero::belongsTo('heroid')
			);
	}
}

class SecretIdentity extends DomainObject
{
	public function properties()
	{
		return array(
			'identityid' => new Types\Sequence(),
			'realname' => new Types\String(),
			);
	}

	public function relationships()
	{
		return array(
			'Hero' => Hero::hasOne('identityid')
			);
	}
}

class RelationshipsTestCase extends \Pheasant\Tests\DbTestCase
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

	public function testHasOneRelationship()
	{
		$hero = new Hero(array('alias'=>'Spider Man'));
		$hero->save();

		$identity = new SecretIdentity(array('realname'=>'Peter Parker'));
		$identity->Hero = $hero;
		$identity->save();

		$this->assertEqual($hero->identityid, $identity->identityid);
		$this->assertTrue($hero->SecretIdentity->equals($identity));
		$this->assertTrue($identity->Hero->equals($hero));
	}

	public function testPropertyReferencesResolvedInMapping()
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
}
