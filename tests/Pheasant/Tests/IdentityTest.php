<?php

namespace Pheasant\Tests;

use Pheasant\Tests\Examples\Animal;

class IdentityTest extends \Pheasant\Tests\MysqlTestCase
{
    public function testEqualIdentities()
    {
        $animal1 = new Animal(array('id' => 1, 'type' => 'llama'));
        $animal2 = new Animal(array('id' => 1, 'type' => 'goat'));
        $this->assertTrue($animal1->identity()->equals($animal2->identity()));
    }

    public function testUnequalIdentities()
    {
        $animal1 = new Animal(array('id' => 1, 'type' => 'llama'));
        $animal2 = new Animal(array('id' => 2, 'type' => 'llama'));
        $this->assertFalse($animal1->identity()->equals($animal2->identity()));
    }

    public function testIdentityAsString()
    {
        $animal = new Animal(array('id' => 1, 'type' => 'llama'));
        $this->assertEquals(
            "Pheasant\Tests\Examples\Animal[id=1]",
            $animal->identity()->__toString()
            );
    }
}
