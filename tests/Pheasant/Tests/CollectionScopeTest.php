<?php

namespace Pheasant\Tests;

use \Pheasant\Collection;
use \Pheasant\Query\Query;
use \Pheasant\Tests\Examples\Animal;

class CollectionScopeTest extends \Pheasant\Tests\MysqlTestCase
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

    public function testSimpleScope()
    {
        $frogs = Animal::all()->frogs();
        $this->assertEquals(2, $frogs->count());
    }

    public function testMultipleFilterCalls(){
        $frogs = Animal::all()->filter('id = ?', Animal::all()->last()->id)->frogs();
        $this->assertEquals($frogs->one()->name, Animal::all()->last()->name);
    }

    public function testPassingArgsToScope(){
        $frogs_by_type = Animal::all()->filter('id = ?', Animal::all()->last()->id)->by_type('frog');
        $frogs = Animal::all()->filter('id = ?', Animal::all()->last()->id)->frogs();

        $this->assertEquals($frogs->one()->name, $frogs_by_type->one()->name);
    }


    public function testNonExistantProperty()
    {
        $this->setExpectedException('BadMethodCallException');
        Animal::all()->llamas();
    }
}
