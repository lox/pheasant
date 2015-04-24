<?php

namespace Pheasant\Tests;

use \Pheasant\Types;

class TypesTest extends \Pheasant\Tests\MysqlTestCase
{
    public function testInteger()
    {
        $type = new Types\Integer(10);
        $this->assertMysqlColumnSql('`test` int(10)', $type);

        $type = new Types\Integer();
        $this->assertMysqlColumnSql('`test` int', $type);
    }

    public function testSmallInteger()
    {
        $type = new Types\SmallInteger(10);
        $this->assertMysqlColumnSql('`test` smallint(10)', $type);

        $type = new Types\SmallInteger();
        $this->assertMysqlColumnSql('`test` smallint', $type);
    }

    public function testBigInteger()
    {
        $type = new Types\BigInteger(10);
        $this->assertMysqlColumnSql('`test` bigint(10)', $type);

        $type = new Types\BigInteger();
        $this->assertMysqlColumnSql('`test` bigint', $type);
    }

    public function testIntegerPrimaryNotNull()
    {
        $type = new Types\Integer(10, 'notnull primary');

        $this->assertTrue($type->options()->notnull);
        $this->assertTrue($type->options()->primary);
        $this->assertMysqlColumnSql('`test` int(10) not null primary key', $type);
    }

    public function testDefaultSequence()
    {
        $type = new Types\Sequence();

        $this->assertEquals($type->options()->sequence, null);
        $this->assertEquals($type->options()->primary, true);
        $this->assertMysqlColumnSql('`test` int(11) primary key not null', $type);
    }

    public function testDecimal()
    {
        $type = new Types\Decimal(12, 4);

        $this->assertMysqlColumnSql('`test` decimal(12,4)', $type);
    }

    public function testCharacter()
    {
        $type = new Types\Character(4);

        $this->assertMysqlColumnSql('`test` char(4)', $type);
    }


    public function testString()
    {
        $type = new Types\String(255);
        $this->assertMysqlColumnSql('`test` varchar(255)', $type);

        $type = new Types\String(65000);
        $this->assertMysqlColumnSql('`test` text', $type);

        $type = new Types\String(10000000);
        $this->assertMysqlColumnSql('`test` mediumtext', $type);

        $type = new Types\String(100000000);
        $this->assertMysqlColumnSql('`test` longtext', $type);


        $type = new Types\String(65000, 'required');
        $this->assertMysqlColumnSql('`test` text not null', $type);
    }

    public function testBoolean()
    {
        $type = new Types\Boolean();
        $this->assertMysqlColumnSql('`test` boolean', $type);

        $type = new Types\Boolean('notnull');
        $this->assertMysqlColumnSql('`test` boolean not null', $type);
    }

    public function testSet()
    {
        $type = new Types\Set(array('foo', 'bar'));
        $this->assertMysqlColumnSql("`test` set('foo','bar')", $type);

        $type = new Types\Set(array('foo', 'bar'), 'notnull');
        $this->assertMysqlColumnSql("`test` set('foo','bar') not null", $type);
    }

    public function assertMysqlColumnSql($sql, $type)
    {
        $this->assertEquals($type->columnSql('test', new \Pheasant\Database\MysqlPlatform()), $sql);
    }
}
