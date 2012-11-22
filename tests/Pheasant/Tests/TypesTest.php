<?php

namespace Pheasant\Tests;

use \Pheasant\Types;
use \Pheasant\Database\Mysqli;

class TypesTest extends \Pheasant\Tests\MysqlTestCase
{
  public function testInteger()
  {
    $type = new Types\Integer(10);
    $this->assertEquals($type->type, Types\Integer::TYPE);
    $this->assertEquals($type->length, 10);

    // check the type conversion
    $map = new Mysqli\TypeMap(array('test'=>$type));
    $this->assertEquals($map->columnDef('test'),
      '`test` int(10)');
  }

  public function testIntegerPrimaryNotNull()
  {
    $type = new Types\Integer(10, 'notnull primary');
    $this->assertEquals($type->type, Types\Integer::TYPE);
    $this->assertEquals($type->length, 10);
    $this->assertTrue($type->options->notnull);
    $this->assertTrue($type->options->primary);

    // check the type conversion
    $map = new Mysqli\TypeMap(array('test'=>$type));
    $this->assertEquals($map->columnDef('test'),
      '`test` int(10) not null primary key');
  }

  public function testDefaultSequence()
  {
    $type = new Types\Sequence();
    $this->assertEquals($type->type, Types\Integer::TYPE);
    $this->assertEquals($type->length, 11);
    $this->assertEquals($type->options->sequence, null);
    $this->assertEquals($type->options->primary, true);

    // check the type conversion
    $map = new Mysqli\TypeMap(array('test'=>$type));
    $this->assertEquals($map->columnDef('test'),
      '`test` int(11) primary key not null');
  }

  public function testDecimal()
  {
    $type = new Types\Decimal(12, 4);
    $this->assertEquals($type->type, Types\Decimal::TYPE);
    $this->assertEquals($type->length, 12);
    $this->assertEquals($type->scale, 4);

    // check the type conversion
    $map = new Mysqli\TypeMap(array('test'=>$type));
    $this->assertEquals($map->columnDef('test'),
      '`test` decimal(12,4)');
  }

  public function testCharacter()
  {
    $type = new Types\Character(4);
    $this->assertEquals($type->type, Types\Character::TYPE);
    $this->assertEquals($type->length, 4);

    // check the type conversion
    $map = new Mysqli\TypeMap(array('test'=>$type));
    $this->assertEquals($map->columnDef('test'),
      '`test` char(4)');
  }

  public function testBoolean()
  {
    $type = new Types\Boolean();
    $this->assertEquals($type->type, Types\Boolean::TYPE);
    $this->assertEquals($type->length, NULL);

    // check the type conversion
    $map = new Mysqli\TypeMap(array('test'=>$type));
    $this->assertEquals($map->columnDef('test'),
      '`test` boolean');

    $notnull = new Types\Boolean('notnull');

    // check not-null works
    $map = new Mysqli\TypeMap(array('test'=>$notnull));
    $this->assertEquals($map->columnDef('test'),
      '`test` boolean not null');

  }
}
