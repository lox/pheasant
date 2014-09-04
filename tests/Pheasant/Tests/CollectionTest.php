<?php

namespace Pheasant\Tests;

use \Pheasant\Collection;
use \Pheasant\Query\Query;
use \Pheasant\Tests\Examples\Animal;

class CollectionTest extends \Pheasant\Tests\MysqlTestCase
{
    public function setUp()
    {
        parent::setUp();

        $migrator = new \Pheasant\Migrate\Migrator();
        $migrator
            ->create('animal', Animal::schema())
            ;

        Animal::import(array(
            array('name'=>'Llama', 'type'=>'llama'),
            array('name'=>'Blue Frog', 'type'=>'frog'),
            array('name'=>'Red Frog', 'type'=>'frog'),
        ));
    }

    public function testIteratingEmpty()
    {
        foreach(Animal::find('type=?','mongoose') as $animal) {
        }
    }

    public function testOneWhenZero()
    {
        $this->setExpectedException('Pheasant\NotFoundException');
        $results = Animal::findByName('nonexistent')->one();
    }

    public function testOneWhenMany()
    {
        $this->setExpectedException('Pheasant\ConstraintException');
        $results = Animal::find()->one();
    }

    public function testLimit()
    {
        $results = Animal::find()->limit(2)->toArray();
        $this->assertCount(2, $results);
    }

    public function testOrder()
    {
        $resultsOrder = Animal::find()->order('type ASC')->toArray();
        $resultsOrderBy = Animal::find()->orderBy('type ASC')->toArray();
        $this->assertEquals($resultsOrder, $resultsOrderBy);
    }

    public function testOrderBy()
    {
        $results = Animal::find()->orderBy('type ASC')->toArray();
        $this->assertEquals('frog', $results[0]->type);
        $this->assertEquals('llama', $results[2]->type);

        $results = Animal::find()->orderBy('type DESC')->toArray();
        $this->assertEquals('llama', $results[0]->type);
        $this->assertEquals('frog', $results[2]->type);
    }

    public function testSelect()
    {
        $results = Animal::find()->select('type');

        $this->assertEquals('llama', $results[0]->type);
        $this->assertEquals('frog', $results[1]->type);

        // check that missing columns return null
        $this->assertNull($results[0]->name);
    }

    public function testSelectColumn()
    {
        $results = Animal::find()->select('type')->column('type')->toArray();
        $this->assertEquals(array('llama','frog','frog'), $results);

        $results = Animal::find()->select('type')->column('type')->unique();
        $this->assertEquals(array('llama','frog'), $results);
    }

    public function testOrCreate()
    {
        $results = Animal::find('name=?', 'Orangutan');
        $this->assertCount(0, $results);

        $results = Animal::find('name=?', 'Orangutan')->orCreate(array(
            'name' => 'Orangutan', 'type'=>'primate'
        ));
        $this->assertCount(1, $results);
        $this->assertEquals('Orangutan', $results->one()->name);
    }

    public function testFirst()
    {
        $llama = Animal::find()->first();
        $this->assertEquals('llama', $llama->type);
    }

    public function testLast()
    {
        $frog = Animal::find()->last();
        $this->assertEquals('Red Frog', $frog->name);
    }

    public function testFirstOnEmptyCollection()
    {
        $this->setExpectedException('Pheasant\NotFoundException');
        Animal::find('name=?', 'Dodo')->first();
    }

    public function testLastOnEmptyCollection()
    {
        $this->setExpectedException('Pheasant\NotFoundException');
        Animal::find('name=?', 'Dodo')->last();
    }

    public function testIteratingAndSaving()
    {
        $animals = Animal::all()->filter('type="frog"');

        foreach($animals as $animal) {
            $animal->type = 'Test';
            $animal->save();
        }

        $this->assertCount(2, Animal::findByType('Test'));
    }

    public function testSaving()
    {
        Animal::all()->save(function($animal) {
            if($animal->type == 'frog') {
                $animal->type = 'tadpole';
            }
        });

        $this->assertCount(2, Animal::findByType('tadpole'));
    }

    public function testDelete()
    {
        Animal::findByType('frog')->delete();

        $this->assertCount(1, Animal::all());
    }

    public function testAggregateFunctions()
    {
        $this->assertEquals(3, Animal::all()->max('id'));
        $this->assertEquals(1, Animal::all()->min('id'));
        $this->assertEquals(6, Animal::all()->sum('id'));
    }
}
