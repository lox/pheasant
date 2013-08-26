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

    public function testAsync()
    {
        \Pheasant\Database\Mysqli\Connection::$debug = true;

        $dsn = getenv('PHEASANT_TEST_DSN') ?: 'mysql://root@localhost/pheasanttest?charset=utf8';
        \Pheasant::instance()->connections()
            ->addConnection('async1', $dsn)
            ->addConnection('async2', $dsn);

        var_dump('------');
        ob_flush();

        $c1 = Animal::find('1=1')
            ->filter('!sleep(1)')
            ->async('async1')
            ;

        $c2 = Animal::find('1=1')
            ->filter('!sleep(2)')
            ->async('async2')
            ;

        // Do some stuff in the meantime
        echo "Count to 3 while queries are running..\n";
        foreach(range(1,3) as $i) {
            echo $i."\n";
            ob_flush();
            sleep(1);
        }

        // Should be ready..
        foreach ($c1 as $a) {
            echo "1 - $a->name\n";
            ob_flush();
        }

        // Still waiting..
        foreach ($c2 as $a) {
            echo "2 - $a->name\n";
            ob_flush();
        }

        \Pheasant\Database\Mysqli\Connection::$debug = false;
    }

    public function testIteratingEmpty()
    {
        foreach(Animal::find('type=?','mongoose') as $animal) {
        }
    }

    public function testOne()
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

}
