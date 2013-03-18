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
    }

    public function testOrder()
    {
        Animal::create(array('type'=>'llama'))->save();
        Animal::create(array('type'=>'frog'))->save();

        $results = Animal::find()->order('type ASC')->toArray();
        $this->assertEquals('frog', $results[0]->type);
        $this->assertEquals('llama', $results[1]->type);

        $results = Animal::find()->order('type DESC')->toArray();
        $this->assertEquals('llama', $results[0]->type);
        $this->assertEquals('frog', $results[1]->type);
    }
}
