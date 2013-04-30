<?php

namespace Pheasant\Tests;

use \Pheasant\Types\Sequence;
use \Pheasant\Types\String;
use \Pheasant\Tests\Examples\Person;

class DomainObjectSequenceTest extends \Pheasant\Tests\MysqlTestCase
{
    public function setUp()
    {
        parent::setUp();

        $table = $this->table('person', array(
            'personid' => new Sequence(),
            'name' => new String(),
            ));
    }

    public function testSequencePrimaryKey()
    {
        $person = new Person();
        $person->save();

        $this->assertEquals(1, $person->personid);

        $person->name = "Frank";
        $person->save();

        $this->assertEquals(1, $person->personid);
        $this->assertEquals("Frank", $person->name);
    }

    public function testSequencesPersistAfterSave_Bug57()
    {
        $person = Person::create(array());

        $this->assertEquals(1, $person->personid);

        $found = Person::byId(1);
        $this->assertEquals(1, $found->personid);

        $found->save();
        $this->assertEquals(1, $found->personid);
    }

    // FIXME: is this desired behaviour?
    public function testSequenceFaileWhenManuallySet()
    {
        $person = new Person();
        $person->personid = 24;
        $person->save();

        $this->assertEquals(24, $person->personid);
    }
}
