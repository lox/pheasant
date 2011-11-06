<?php

namespace Pheasant\Tests\Types;

use \Pheasant\Types;
use \Pheasant\Database\Mysqli;

require_once(__DIR__.'/../vendor/simpletest/autorun.php');
require_once(__DIR__.'/base.php');

class TypesTestCase extends \Pheasant\Tests\DbTestCase
{
	public function testInteger()
	{
		$type = new Types\Integer(10);
		$this->assertEqual($type->type, Types\Integer::TYPE);
		$this->assertEqual($type->length, 10);

		// check the type conversion
		$map = new Mysqli\TypeMap(array('test'=>$type));
		$this->assertEqual($map->columnDef('test'),
			'`test` int(10)');
	}

	public function testIntegerPrimaryNotNull()
	{
		$type = new Types\Integer(10, 'notnull primary');
		$this->assertEqual($type->type, Types\Integer::TYPE);
		$this->assertEqual($type->length, 10);
		$this->assertTrue($type->options->notnull);
		$this->assertTrue($type->options->primary);

		// check the type conversion
		$map = new Mysqli\TypeMap(array('test'=>$type));
		$this->assertEqual($map->columnDef('test'),
			'`test` int(10) not null primary key');
	}

	public function testDefaultSequence()
	{
		$type = new Types\Sequence();
		$this->assertEqual($type->type, Types\Integer::TYPE);
		$this->assertEqual($type->length, 11);
		$this->assertEqual($type->options->sequence, null);
		$this->assertEqual($type->options->primary, true);

		// check the type conversion
		$map = new Mysqli\TypeMap(array('test'=>$type));
		$this->assertEqual($map->columnDef('test'),
			'`test` int(11) primary key not null');
	}

	public function testDecimal()
	{
		$type = new Types\Decimal(12, 4);
		$this->assertEqual($type->type, Types\Decimal::TYPE);
		$this->assertEqual($type->length, 12);
		$this->assertEqual($type->scale, 4);

		// check the type conversion
		$map = new Mysqli\TypeMap(array('test'=>$type));
		$this->assertEqual($map->columnDef('test'),
			'`test` decimal(12,4)');
	}

	public function testCharacter()
	{
		$type = new Types\Character(4);
		$this->assertEqual($type->type, Types\Character::TYPE);
		$this->assertEqual($type->length, 4);

		// check the type conversion
		$map = new Mysqli\TypeMap(array('test'=>$type));
		$this->assertEqual($map->columnDef('test'),
			'`test` char(4)');
	}
}
