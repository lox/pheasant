<?php

namespace Pheasant\Tests;

use \Pheasant\DomainObject;
use \Pheasant\Types;
use \Pheasant\Mapper\RowMapper;
use \Pheasant\Tests\Examples\Animal;
use \Pheasant\Tests\Examples\AnotherAnimal;
use \Pheasant\Tests\Examples\AnimalWithNameDefault;

use Mockery as m;

class Blah extends DomainObject
{
	public static $callbacks = array();

	public function properties()
	{
		return array(
			'blahid' => new Types\Sequence(),
			'text' => new Types\String(),
		);
	}

	public function beforeCreate() { $fn = self::$callbacks[__FUNCTION__]; $fn(__FUNCTION__); }
	public function beforeUpdate() { $fn = self::$callbacks[__FUNCTION__]; $fn(__FUNCTION__); }
	public function beforeSave() { $fn = self::$callbacks[__FUNCTION__]; $fn(__FUNCTION__); }

	public function afterInitialize() { $fn = self::$callbacks[__FUNCTION__]; $fn(__FUNCTION__); }
	public function afterCreate() { $fn = self::$callbacks[__FUNCTION__]; $fn(__FUNCTION__); }
	public function afterUpdate() { $fn = self::$callbacks[__FUNCTION__]; $fn(__FUNCTION__); }
	public function afterSave() { $fn = self::$callbacks[__FUNCTION__]; $fn(__FUNCTION__); }
}


class DomainObjectTest extends \Pheasant\Tests\MysqlTestCase
{
	public function setUp()
	{
		parent::setUp();

		$migrator = new \Pheasant\Migrate\Migrator();
		$migrator
			->create('animal', Animal::schema())
			;
	}

	public function testDefaultProperties()
	{
		$animal = new Animal();
		$this->assertEquals($animal->type, 'llama');
		$this->assertEquals($animal->toArray(),
			array('id'=>NULL, 'type'=>'llama', 'name'=>null));

		$llama = new Animal(array('type'=>'llama'));
		$frog = new Animal(Array('type'=>'frog'));

		$this->assertTrue($llama->equals($animal));
		$this->assertFalse($llama->equals($frog));
	}

	public function testPropertyIsset()
	{
		$animal = new Animal(array('name'=>'bob'));

		$this->assertTrue(isset($animal->type));
		$this->assertTrue(isset($animal->name));

		$this->assertFalse(isset($animal->unknown));
	}

	/**
	 * @expectedException Pheasant\Exception
	 */
	public function testGettingUnknownProperty()
	{
		$animal = Animal::import(array(array('type'=>'Hippo')));
		$animal[0]->unknownKey;
	}

	/**
	 * @expectedException Pheasant\Exception
	 */
	public function testSavingUnknownProperty()
	{
		// try non-saved objects
		$another = new Animal();
		$another->unknown;
		$instance->save();
	}

	public function testInitializeDefaults()
	{
		$animal = new AnotherAnimal();
		$animal->type = 'llama';
		$animal->save();

		$this->assertEquals($animal->type, 'llama');
		$this->assertEquals($animal->tableName(), 'animal');
	}

	public function testCountIsConsistent()
	{
		$animal = Animal::import(array(
			array('type'=>'Hippo'),
			array('type'=>'Cat'),
			array('type'=>'Llama'),
			array('type'=>'Raptor'),
		));

		$awesome = Animal::find("type = 'Cat' or type = 'Llama'");
		$this->assertEquals($awesome->count(), 2);

		$scary = Animal::find("type = ?", 'Raptor');
		$this->assertEquals($scary->count(), 1);
		$this->assertEquals($awesome->count(), 2);
		$this->assertEquals($awesome[1]->type, 'Llama');
		$this->assertEquals($scary[0]->type, 'Raptor');
		$this->assertEquals($awesome[0]->type, 'Cat');
	}

	public function testIssue11_DefaultValuesArePersistedInDatabase()
	{
		$animal = new AnimalWithNameDefault(array('type'=>'horse'));

		$this->assertEquals($animal->name, 'blargh');
		$animal->save();

		$this->assertRowCount(1, $this->connection()->table('animal')->query(array(
			'id' => $animal->id,
			'type' => 'horse',
			'name' => 'blargh',
		)));

		$horse = AnimalWithNameDefault::byId(1);
		$this->assertEquals($animal->name, 'blargh');
	}

	public function testForBug()
	{
		$migrator = new \Pheasant\Migrate\Migrator();
		$migrator->create('blah', Blah::schema());

		$this->_expectEventsForBlah('afterInitialize');
		$blah = new Blah();

		$this->_expectEventsForBlah('beforeCreate', 'beforeSave', 'afterCreate', 'afterSave');
		$blah->save();

		$this->_expectEventsForBlah('beforeUpdate', 'beforeSave', 'afterUpdate', 'afterSave');
		$blah->text = 'aaa';
		$blah->save();

		// Finding a domain object and updating it should trigger these events
		$this->_expectEventsForBlah('beforeUpdate', 'beforeSave', 'afterUpdate', 'afterSave');
		$blahs = Blah::find();
		$blah = $blahs[0];
		$blah->text = 'bbb';
		$blah->save();
	}

	private function _expectEventsForBlah(/* varargs */)
	{
		$callbacks = array();
		foreach (func_get_args() as $e) {
			$m = m::mock('Blah');
			$m->shouldReceive($e)->atLeast()->times(1);
			$callbacks[$e] = function($name) use ($m, $e) { $m->$e(); };
		}
		Blah::$callbacks = $callbacks;
	}
}

