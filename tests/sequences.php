<?php

namespace pheasant\tests\sequences;
use \pheasant\database\mysqli\SequencePool;
use \pheasant\DomainObject;

require_once('autorun.php');
require_once(__DIR__.'/base.php');

class Person extends DomainObject
{
	protected function configure($schema, $props, $rels)
	{
		$schema->table('person');
		$props->sequence('personid');
	}
}

class SequenceTestCase extends \pheasant\tests\MysqlTestCase
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

class DomainObjectSequenceTestCase extends \pheasant\tests\MysqlTestCase
{
	public function testSequencePrimaryKey()
	{
		$person = new Person();
		$person->save();

		$this->assertEqual(1, $person->personid);
	}
}
