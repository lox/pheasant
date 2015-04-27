<?php

namespace Pheasant\Tests;

use \Pheasant\Tests\Examples\Animal;

class LockingTest extends \Pheasant\Tests\MysqlTestCase
{
    public function setUp()
    {
        parent::setUp();

        $migrator = new \Pheasant\Migrate\Migrator();
        $migrator
            ->create('animal', Animal::schema())
            ;

        $this->queries = array();
        $test = $this;
        $this->connection()->filterChain()->onQuery(function($sql) use($test) {
            $test->queries []= $sql;
            return $sql;
        });
    }

    public function testLockingAnInstance()
    {
        $animal = Animal::create(array('type'=>'Llama'));
        $animal->transaction(function($animal) {
            $animal->lock();
        });

        $this->assertTrue(in_array(
            "SELECT * FROM `animal` AS `Animal` WHERE ((`id`='1')) FOR UPDATE",
            $this->queries
        ));
    }

    public function testLockingAnInstanceCallsCallback()
    {
        $animal = Animal::create(array('type'=>'Llama'));
        $object = new \stdClass();
        $object->called = false;

        // fudge the data in the background
        $this->connection()->execute('UPDATE animal SET type="walrus" WHERE id=1');

        $animal->transaction(function($animal) use($object) {
            $animal->lock(function($locked) use($object) {
                $object->called = true;
            });
        });

        $this->assertTrue($object->called);
    }

    public function testLockingAnUnsavedInstanceThrowsExceptions()
    {
        $animal = new Animal();
        $animal->type = 'llama';

        $this->setExpectedException('\Pheasant\Locking\LockingException');
        $animal->transaction(function($animal) {
            $animal->lock();
        });
    }
}

