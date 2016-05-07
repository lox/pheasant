<?php

namespace Pheasant\Tests\Relationships;

use \Pheasant\Tests\Examples\Hero;
use \Pheasant\Tests\Examples\Power;
use \Pheasant\Tests\Examples\SecretIdentity;

class IncludesTest extends \Pheasant\Tests\MysqlTestCase
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

        $this->pheasant
            ->connection()
            ->execute(
                'INSERT INTO sequences (name, id) VALUES (?, ?)',
                array('SECRETIDENTITY_ID_SEQ', 100)
            );

        $spiderman = Hero::createHelper('Spider Man', 'Peter Parker', array(
            'Super-human Strength', 'Spider Senses'
        ));
        $superman = Hero::createHelper('Super Man', 'Clark Kent', array(
            'Super-human Strength', 'Invulnerability'
        ));
        $batman = Hero::createHelper('Batman', 'Bruce Wayne', array(
            'Richness', 'Super-human Intellect'
        ));
    }

    public function testIncludesHitsCache()
    {
        $queries = 0;

        $this->connection()->filterChain()->onQuery(function ($sql) use (&$queries) {
            ++$queries;

            return $sql;
        });

        // the first lookup of SecretIdentity should cache all the rest
        $heros = Hero::all()->includes(array('SecretIdentity'))->toArray();
        $this->assertNotNull($heros[0]->SecretIdentity);

        // these should be from cache
        $queries = 0;
        $this->assertNotNull($heros[1]->SecretIdentity);
        $this->assertNotNull($heros[2]->SecretIdentity);

        $this->assertEquals(0, $queries, "this should have hit the cache");
    }

    public function testNestedIncludesHitsCache()
    {
        $queries = 0;

        $this->connection()->filterChain()->onQuery(function ($sql) use (&$queries) {
            ++$queries;
            return $sql;
        });

        // the first lookup of SecretIdentity should cache all the rest
        $powers = Power::all()->includes(
          array('Hero' => array('SecretIdentity')))->toArray();
        $this->assertNotNull($powers[0]->Hero->SecretIdentity);

        // these should be from cache
        $queries = 0;
        foreach ($powers as $power) {
            $this->assertNotNull($power->Hero->SecretIdentity);
        }
        $this->assertEquals(0, $queries, "this should have hit the cache");
    }
}
