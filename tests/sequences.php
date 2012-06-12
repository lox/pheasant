<?php

namespace Pheasant\Tests\Sequences;
use \Pheasant\Mapper\RowMapper;
use \Pheasant\Database\Mysqli\SequencePool;
use \Pheasant\DomainObject;
use \Pheasant\Types\Sequence;
use \Pheasant\Types\String;

require_once(__DIR__.'/../vendor/lastcraft/simpletest/autorun.php');
require_once(__DIR__.'/base.php');

class Person extends DomainObject
{
	public static function initialize($builder, $pheasant)
	{
		$pheasant
			->register(__CLASS__, new RowMapper('person'));

		$builder
			->properties(array(
				'personid' => new Sequence('personid'),
				'name' => new String(),
			));
	}
}

class SequenceTestCase extends \Pheasant\Tests\MysqlTestCase
{
	public function setUp()
	{
		$this->pool = new SequencePool($this->pheasant->connection());
		$this->pool
			->initialize()
			->clear()
			;

		$this->assertTableExists(SequencePool::TABLE);
	}

	public function testSequences()
	{
		$this->assertEqual(1, $this->pool->next('my_sequence'));
		$this->assertEqual(2, $this->pool->next('my_sequence'));
		$this->assertEqual(3, $this->pool->next('my_sequence'));
		$this->assertEqual(4, $this->pool->next('my_sequence'));
	}
}

class DomainObjectSequenceTestCase extends \Pheasant\Tests\MysqlTestCase
{
	public function setUp()
	{
		$table = $this->table('person', array(
			'personid' => new Sequence(),
			'name' => new String(),
			));
	}

	public function testSequencePrimaryKey()
	{
		$person = new Person();
		$person->save();

		$this->assertEqual(1, $person->personid);

		$person->name = "Frank";
		$person->save();

		$this->assertEqual(1, $person->personid);
		$this->assertEqual("Frank", $person->name);
	}

	//FIXME: what behaviour is optimal for this case?
	/*
	public function testSequenceManuallySet()
	{
		$person = new Person();
		$person->personid = 24;
		$person->save();

		$this->assertEqual(24, $person->personid);
	}
	*/
}
