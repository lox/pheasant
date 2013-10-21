<?php

namespace Pheasant\Tests;

class MysqlTestCase extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $dsn = getenv('PHEASANT_TEST_DSN')
            ?: 'mysql://root@localhost/pheasanttest?charset=utf8';

        // initialize a new pheasant
        $this->pheasant = \Pheasant::setup($dsn);

        // wipe sequence pool
        $this->pheasant->connection()
            ->sequencePool()
            ->initialize()
            ->clear()
            ;
    }

    public function tearDown()
    {
        $this->pheasant->connection()->close();
    }

    // Helper to return a connection
    public function connection()
    {
        return $this->pheasant->connection();
    }

    // Helper to initialize a domain object
    public function initialize($class, $callback=null)
    {
        return $this->pheasant->initialize($class, $callback);
    }

    // Helper to drop and re-create a table
    public function table($name, $columns)
    {
        $table = $this->pheasant->connection()->table($name);

        if($table->exists()) $table->drop();

        $table->create($columns);

        $this->assertTableExists($name);

        return $table;
    }

    public function migrate($tableName, $schema)
    {
        $migrator = new \Pheasant\Migrate\Migrator();
        $migrator->create($tableName, $schema);
    }

    public function assertConnectionExists()
    {
        $this->assertTrue($this->pheasant->connection());
    }

    public function assertTableExists($table)
    {
        $this->assertTrue($this->pheasant->connection()->table($table)->exists());
    }

    public function assertRowCount($count, $sql)
    {
        if(is_object($sql))
            $sql = $sql->toSql();

        $result = $this->connection()->execute($sql);
        $this->assertEquals($result->count(), $count);
    }
}
