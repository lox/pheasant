<?php

namespace Pheasant\Tests;

use \Pheasant\Tests\Examples\Animal;
use \Pheasant\Tests\Examples\AnotherAnimal;
use \Pheasant\Tests\Examples\AnimalWithNameDefault;

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
}
