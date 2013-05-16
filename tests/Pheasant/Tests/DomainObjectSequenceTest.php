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

    public function testSequenceFaileWhenManuallySet()
    {
        $person = new Person();
        $person->personid = 24;
        $person->save();

        // FIXME: is this desired behaviour?
        $this->assertEquals(1, $person->personid);
    }
}
