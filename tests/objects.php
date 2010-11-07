<?php

namespace Pheasant\Tests\Objects;

use \Pheasant\DomainObject;
use \Pheasant\Types;
use \Pheasant\Mapper\RowMapper;

require_once('autorun.php');
require_once(__DIR__.'/base.php');

class Animal extends DomainObject
{
	public static function initialize($builder, $pheasant)
	{
		$pheasant
			->register(__CLASS__, new RowMapper('animal'));

		$builder
			->properties(array(
				'id' => new Types\Integer(11, 'primary auto_increment'),
				'type' => new Types\String(255, 'required default=llama'),
			));
	}
}

class DomainObjectTestCase extends \Pheasant\Tests\MysqlTestCase
{
	public function setUp()
	{
		$migrator = new \Pheasant\Migrate\Migrator();
		$migrator
			->create('animal', Animal::schema())
			;
	}

	public function testDefaultProperties()
	{
		$animal = new Animal();
		$this->assertEqual($animal->type, 'llama');
		$this->assertEqual($animal->toArray(),
			array('id'=>NULL, 'type'=>'llama'));

		$llama = new Animal(array('type'=>'llama'));
		$frog = new Animal(Array('type'=>'frog'));

		$this->assertTrue($llama->equals($animal));
		$this->assertFalse($llama->equals($frog));
	}

	public function testUnknownProperty()
	{
		$animal = Animal::import(array(array('type'=>'Hippo')));
		$this->expectException();
        $animal[0]->unknownKey;

        // try non-saved objects
        $another = new Animal();
        $this->expectException();
        $another->unknown;
        return $instance->save();
	}
}

