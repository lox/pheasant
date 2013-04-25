<?php

namespace Pheasant\Tests;

class EnumeratorTest extends \Pheasant\Tests\MysqlTestCase
{
    public function setUp()
    {
        parent::setUp();
    }

    public function testEnumerating()
    {
        $dir = __DIR__.'/Examples';
        $files = array_map(function($f) { return substr(basename($f),0,-4); },
            glob($dir.'/*.php'));

        $enumerator = new \Pheasant\Migrate\Enumerator($dir);
        $objects = iterator_to_array($enumerator);

        foreach($files as $file)
            $this->assertContains('\\Pheasant\\Tests\\Examples\\'.$file, $objects);
    }
}
