<?php

namespace Pheasant\Tests;

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

    public function testLimit()
    {
        $results = Animal::find()->limit(2)->toArray();
        $this->assertCount(2, $results);
    }

    public function testOrder()
    {
        $results = Animal::find()->order('type ASC')->toArray();
        $this->assertEquals('frog', $results[0]->type);
        $this->assertEquals('llama', $results[2]->type);

        $results = Animal::find()->order('type DESC')->toArray();
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
}
