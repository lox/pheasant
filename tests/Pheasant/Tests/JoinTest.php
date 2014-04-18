<?php

namespace Pheasant\Tests\Relationships;

use \Pheasant\Tests\Examples\Hero;
use \Pheasant\Tests\Examples\Power;
use \Pheasant\Tests\Examples\SecretIdentity;

class JoinTest extends \Pheasant\Tests\MysqlTestCase
{
    public function setUp()
    {
        parent::setUp();

        $migrator = new \Pheasant\Migrate\Migrator();
        $migrator
            ->create('hero', Hero::schema())
            ->create('power', Power::schema())
            ->create('secretidentity', SecretIdentity::schema())
            ;

        $this->spiderman = Hero::createHelper('Spider Man', 'Peter Parker', array(
            'Super-human Strength', 'Spider Senses'
        ));

        $this->superman = Hero::createHelper('Super Man', 'Clark Kent', array(
            'Super-human Strength', 'Invulnerability'
        ));

        $this->batman = Hero::createHelper('Batman', 'Bruce Wayne', array(
            'Richness', 'Super-human Intellect'
        ));
    }

    public function testBasicJoiningResultsInCartesianProduct()
    {
        $collection = Hero::all()->join(array('Powers', 'SecretIdentity'));
        $objects = iterator_to_array($collection);

        // the cartesian product of hero x identity x power
        $this->assertCount(3 * 2, $collection);
    }

    public function testBasicJoiningBringsInAllColumns()
    {
        $collection = Hero::all()->join(array('Powers', 'SecretIdentity'));
        $objects = iterator_to_array($collection);

        $this->assertTrue($collection[0]->has('realname'));
        $this->assertTrue($collection[0]->has('description'));
    }

    public function testJoiningWithUnique()
    {
        $collection = Hero::all()
            ->join(array('Powers', 'SecretIdentity'))
            ->unique();
        $objects = iterator_to_array($collection);

        $this->assertCount(3, $collection);
    }

    public function testJoiningWithGroupBy()
    {
        $collection = Hero::all()
            ->join(array('Powers', 'SecretIdentity'))
            ->groupBy('Hero.id');
        $objects = iterator_to_array($collection);

        $this->assertCount(3, $collection);
    }

    public function testJoiningAndFiltering()
    {
        $collection = Hero::all()
            ->join(array('Powers', 'SecretIdentity'))
            ->filter('SecretIdentity.realname = ?', "Peter Parker")
            ;

        $this->assertCount(1 * 2, $collection);
        $this->assertEquals("Spider Man", $collection[0]->alias);
    }

    public function testNestedJoiningAndFiltering()
    {
        $collection = Power::all()
            ->join(array('Hero'=>array('SecretIdentity'=>array('Hero h2'))))
            ->filter('SecretIdentity.realname = ?', "Peter Parker")
            ;

        $this->assertCount(2, $collection);
        $this->assertEquals('Super-human Strength', $collection[0]->description);
        $this->assertEquals('Spider Senses', $collection[1]->description);
    }
}
