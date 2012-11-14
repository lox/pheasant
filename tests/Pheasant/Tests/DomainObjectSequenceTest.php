<?php

namespace Pheasant\Tests;

use \Pheasant\Mapper\RowMapper;
use \Pheasant\Database\Mysqli\SequencePool;
use \Pheasant\DomainObject;
use \Pheasant\Types\Sequence;
use \Pheasant\Types\String;
use \Pheasant\Tests\Examples\Person;

class DomainObjectSequenceTest extends \Pheasant\Tests\MysqlTestCase
{
	public function setUp()
	{
		parent::setUp();

		$table = $this->table('person', array(
			'personid' => new Sequence(),
			'name' => new String(),
			));
	}

	public function testSequencePrimaryKey()
	{
		$person = new Person();
		$person->save();

		$this->assertEquals(1, $person->personid);

		$person->name = "Frank";
		$person->save();

		$this->assertEquals(1, $person->personid);
		$this->assertEquals("Frank", $person->name);
	}

	//FIXME: what behaviour is optimal for this case?
	/*
	public function testSequenceManuallySet()
	{
		$person = new Person();
		$person->personid = 24;
		$person->save();

		$this->assertEquals(24, $person->personid);
	}
	*/
}
