<?php

namespace Pheasant\Tests;

use \Pheasant\Database\Dsn;

class DsnTest extends \Pheasant\Tests\MysqlTestCase
{
    public function testParsingADsn()
    {
        $dsn = new Dsn("mysqli://user:pass@hostname:3306/mydb");

        $this->assertEquals($dsn->scheme, 'mysqli');
        $this->assertEquals($dsn->user, 'user');
        $this->assertEquals($dsn->pass, 'pass');
        $this->assertEquals($dsn->host, 'hostname');
        $this->assertEquals($dsn->port, 3306);
        $this->assertEquals($dsn->database, 'mydb');
    }

    public function testBuildingADsn()
    {
        $dsn = new Dsn("mysqli://user:pass@hostname:3306/mydb");
        $dsn->host = 'anotherhost';
        $dsn->port = 3307;

        $this->assertEquals($dsn->__toString(), 'mysqli://user:pass@anotherhost:3307/mydb');
    }

    public function testBuildingADsnWithoutDb()
    {
        $dsn = new Dsn("mysqli://user:pass@hostname:3306");

        $this->assertFalse(isset($dsn->database));
        $this->assertEquals($dsn->__toString(), 'mysqli://user:pass@hostname:3306');
    }

    public function testBuildingADsnWithQueryStrings()
    {
        $raw = "mysqli://user:pass@hostname:3306?myparam=llamas&another=blargh";
        $dsn = new Dsn($raw);

        $this->assertEquals($dsn->params['another'], 'blargh');
        $this->assertEquals($dsn->params['myparam'], 'llamas');
        $this->assertEquals($dsn->__toString(), $raw);
    }

}
