<?php

namespace Pheasant\Tests\Relationships;

use \Pheasant\Tests\Examples\Hero;
use \Pheasant\Tests\Examples\Power;
use \Pheasant\Tests\Examples\SecretIdentity;

class RelationshipTest extends \Pheasant\Tests\MysqlTestCase
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
    }

    public function testOneToManyViaPropertySetting()
    {
        $hero = new Hero(array('alias'=>'Spider Man'));
        $hero->save();
        $this->assertEquals(count($hero->Powers), 0);

        // save via property access
        $power = new Power(array('description'=>'Spider Senses'));
        $power->heroid = $hero->id;
        $power->save();
        $this->assertEquals(count($hero->Powers), 1);
        $this->assertTrue($hero->Powers[0]->equals($power));
    }

    public function testOneToManyViaArrayAccess()
    {
        $hero = new Hero(array('alias'=>'Spider Man'));
        $hero->save();
        $this->assertEquals(count($hero->Powers), 0);

        // save via adding
        $power = new Power(array('description'=>'Super-human Strength'));
        $hero->Powers[] = $power;
        $power->save();
        $this->assertEquals(count($hero->Powers), 1);
        $this->assertEquals($power->heroid, 1);
        $this->assertTrue($hero->Powers[0]->equals($power));
    }

    public function testHasOneRelationship()
    {
        $hero = new Hero(array('alias'=>'Spider Man'));
        $hero->save();

        $identity = new SecretIdentity(array('realname'=>'Peter Parker'));
        $identity->Hero = $hero;
        $identity->save();

        $this->assertEquals($hero->identityid, $identity->id);
        $this->assertTrue($hero->SecretIdentity->equals($identity));
        $this->assertTrue($identity->Hero->equals($hero));
    }

    public function testPropertyReferencesResolvedInMapping()
    {
        $identity = new SecretIdentity(array('realname'=>'Peter Parker'));
        $hero = new Hero(array('alias'=>'Spider Man'));

        // set the identityid before it's been saved, still null
        $hero->identityid = $identity->id;

        $identity->save();
        $hero->save();

        $this->assertEquals($identity->id, 1);
        $this->assertEquals($hero->identityid, 1);
    }

    public function testFilteringCollectionsReturnedByRelationships()
    {
        $spiderman = Hero::createHelper('Spider Man', 'Peter Parker', array(
            'Super-human Strength', 'Spider Senses'
        ));
        $superman = Hero::createHelper('Super Man', 'Clark Kent', array(
            'Super-human Strength', 'Invulnerability'
        ));
        $batman = Hero::createHelper('Batman', 'Bruce Wayne', array(
            'Richness', 'Super-human Intellect'
        ));

        $this->assertCount(2, $spiderman->Powers);
        $this->assertCount(1, $spiderman->Powers->filter('description LIKE ?', 'Super-human%')->toArray());
    }

    public function testEmptyRelationshipsWithAllowEmpty()
    {
        $hero = new Hero(array('alias'=>'Spider Man'));
        $hero->save();

        $this->assertNull($hero->SecretIdentity);
    }
    
    public function testEmptyRelationshipsWithoutAllowEmpty()
    {
        $power = new Power(array('description'=>'Spider Senses'));
        $power->save();
        
        $this->setExpectedException('\Pheasant\Exception');
        $foo = $power->Hero;
    }
}
