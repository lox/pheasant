<?php

namespace Pheasant\Tests;

use Pheasant\Tests\Examples\Animal;

class ArrayCacheTest extends \Pheasant\Tests\MysqlTestCase
{
    public function testRoundTripInCache()
    {
        $cache = new \Pheasant\Cache\ArrayCache();
        $animal = new Animal(array('id' => 1, 'type' => 'llama'));

        $cache->add($animal);
        $this->assertTrue($cache->has($animal->identity()));

        $row = $cache->get($animal->identity(), function() {
            throw new \InvalidArgumentException("Missing animal");
        });

        $this->assertEquals($animal, $row);
    }
}
