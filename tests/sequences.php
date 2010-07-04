<?php

namespace Pheasant\Tests\Sequences;
use \Pheasant;
use \Pheasant\Database\Mysqli\SequencePool;
use \Pheasant\DomainObject;

require_once('autorun.php');
require_once(__DIR__.'/base.php');

class Person extends DomainObject
{
	public static function configure($schema, $props, $rels)
	{
		$schema->table('person');
		$props->sequence('personid');
	}
}

class SequenceTestCase extends \Pheasant\Tests\MysqlTestCase
{
	public function setUp()
	{
		$this->pool = new SequencePool($this->connection());
		$this->pool
			->initialize()
			->clear()
			;
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
		$table = Pheasant::connection()->table('person');
		$table
			->integer('personid', 4, array('sequence', 'primary'))
			->string('name')
			->create()
			;
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
