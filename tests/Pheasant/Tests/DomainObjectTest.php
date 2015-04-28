<?php

namespace Pheasant\Tests;

use \Pheasant\Tests\Examples\Animal;
use \Pheasant\Tests\Examples\Order;
use \Pheasant\Tests\Examples\AnotherAnimal;
use \Pheasant\Tests\Examples\AnimalWithNameDefault;

class DomainObjectTest extends \Pheasant\Tests\MysqlTestCase
{
    public function setUp()
    {
        parent::setUp();

        $migrator = new \Pheasant\Migrate\Migrator();
        $migrator
            ->destroy(Animal::schema())
            ->initialize(Animal::schema())
            ->destroy(Order::schema())
            ->initialize(Order::schema())
            ;
    }

    public function testLoad()
    {
        $animal = new Animal();
        $animal->load(array(
            'type' => 'walrus',
            'name' => 'Frank the Walrus',
        ));

        $this->assertEquals($animal->type, 'walrus');
        $this->assertEquals($animal->name, 'Frank the Walrus');
    }

    public function testFilteredLoad()
    {
        $animal = new Animal();
        $animal->load(array(
            'type' => 'walrus',
            'name' => 'Frank the Walrus',
            'inject' => 'Bobby tables; DROP ALL TABLES'
        ), array('type', 'name'));

        $this->assertCount(2, $animal->changes());
        $this->assertEquals($animal->type, 'walrus');
        $this->assertEquals($animal->name, 'Frank the Walrus');
        $this->assertFalse(isset($animal->inject));
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

    public function testImportUsesDefaultProperties()
    {
        $animals = Animal::import(array(
            array('name'=>'Larry Llama')
        ));

        $this->assertEquals('llama', $animals[0]->type);
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
        $this->assertEquals($horse->name, 'blargh');
    }

    public function testObjectTransaction()
    {
        $animal = new Animal(array('type'=>'frog'));

        $animal->transaction(function($animal) {
            $animal->save();
        });

        $this->assertCount(1, Animal::findByType('frog'));
    }

    public function testObjectTransactionNotExecuting()
    {
        $this->assertCount(0, Animal::findByType('llama'));

        $t = \Pheasant::transaction(function() {
            $animal = new Animal(array('type'=>'llama'));
            $animal->save();
        }, false);

        $this->assertCount(0, Animal::findByType('llama'));

        $t->execute();
        $this->assertCount(1, Animal::findByType('llama'));
    }

    public function testReloadWithoutClosure()
    {
        $llama = Animal::create(array('type'=>'llama'));

        // update data in background
        $this->connection()->execute('UPDATE animal SET name="Frank" WHERE id=1');

        $this->assertEquals(null, $llama->name);
        $llama->reload();

        $this->assertEquals('Frank', $llama->name);
    }

    public function testIssetWithBooleanValues()
    {
        $llama = Animal::create(array('name' => false));

        $this->assertTrue(isset($llama->name));
    }

    public function testArrayAccess()
    {
        $llama = Animal::create(array('name' => 'Frank'));
        $this->assertTrue(isset($llama['name']));
        $this->assertEquals("Frank", $llama['name']);

        $llama = Animal::create(array('name' => null));
        $this->assertTrue(isset($llama['name']));
        $this->assertNull($llama['name']);

        $llama['name'] = 'Joe';
        $this->assertEquals("Joe", $llama['name']);
    }

    public function testSettingTheSameValueDoesntTriggerChanged() {
        $animal = new Animal(array('type' => 'horse'));
        $animal->save();
        $animal->load(array('type' => 'horse'));
        $this->assertCount(0, $animal->changes());
    }

    public function testChanged() {
        $animal = new Animal(array('type' => 'horse'));
        $animal->save();
        $animal->load(array('type' => 'frog'));
        $this->assertEquals(array('type'=>'frog'), $animal->changes());
    }

    public function testStringCoercion()
    {
        $llama = Animal::create(array('id' => 123));
        $this->assertEquals('Pheasant\Tests\Examples\Animal[id=123]', (string) $llama);
    }

    public function testOverridenProperties()
    {
        $counter = 0;
        $llama = Animal::create(array('id' => 123));
        $llama->override('type', function() use(&$counter) {
            $counter++;
            return 'llama'.$counter;
        });

        $this->assertEquals('llama1', $llama->type);
        $this->assertEquals('llama2', $llama->type);
        $this->assertEquals('llama3', $llama->type);
    }

    public function testDomainObjectsWithReservedNames()
    {
        $order = Order::create(array('id' => 1));
        $this->assertNotNull($order);
        $this->assertEquals(1, Order::all()->count());
    }
}
