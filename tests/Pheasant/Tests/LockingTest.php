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
            "SELECT * FROM animal WHERE ((`id`='1')) FOR UPDATE",
            $this->queries
        ));
    }

    public function testLockingAnInstanceThrowsExceptionsWhenStale()
    {
        $animal = Animal::create(array('type'=>'Llama'));

        // fudge the data in the background
        $this->connection()->execute('UPDATE animal SET type="walrus" WHERE id=1');

        $this->setExpectedException('\Pheasant\Locking\StaleObjectException');
        $animal->transaction(function($animal) {
            $animal->lock();
        });
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

