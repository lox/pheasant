<?php

namespace Pheasant\Tests;

use \Pheasant\Database\FilterChain;

class FilterChainTest extends \Pheasant\Tests\MysqlTestCase
{
    public function testFilteringQuery()
    {
        $connection = \Mockery::mock('\Pheasant\Database\Mysqli\Connection');
        $connection
            ->shouldReceive('execute')
            ->with('SELECT llamas FROM animals')
            ->once();

        $filter = new FilterChain();
        $filter->onQuery(function($sql) {
            return 'SELECT llamas FROM animals';
        });

        $filter->execute('SELECT 1', function($sql) use ($connection) {
            $connection->execute($sql);
        });

        $this->assertTrue(true);
    }

    public function testFilteringResults()
    {
        $connection = \Mockery::mock('\Pheasant\Database\Mysqli\Connection');
        $result = \Mockery::mock('\Pheasant\Database\Mysqli\ResultSet');

        $filter = new FilterChain();
        $results = array();

        $filter->onResult(function($sql, $result, $time) use (&$results) {
            $results []= func_get_args();
        });

        $connection
            ->shouldReceive('execute')
            ->with('SELECT 1')
            ->andReturn($result)
            ->once();

        $filter->execute('SELECT 1', function($sql) use ($connection) {
            return $connection->execute($sql);
        });

        $this->assertEquals(count($results), 1);
        $this->assertEquals('SELECT 1', $results[0][0]);
        $this->assertSame($result, $results[0][1]);
        $this->assertFalse(is_null($results[0][2]));
    }

    public function testCatchingErrors()
    {
        $connection = \Mockery::mock('\Pheasant\Database\Mysqli\Connection');

        $filter = new FilterChain();
        $exceptions = array();

        $filter->onError(function($e) use (&$exceptions) {
            $exceptions []= $e;
        });

        $connection
            ->shouldReceive('execute')
            ->andThrow(new \Exception('Eeeeek!'))
            ;

        try {
            $filter->execute('SELECT 1', function($sql) use ($connection) {
                $connection->execute($sql);
            });

            $this->fail('Exception expected');
        } catch (\Exception $e) {
            $this->assertEquals($e->getMessage(), 'Eeeeek!');
        }

        $this->assertEquals(count($exceptions), 1);
        $this->assertEquals($exceptions[0]->getMessage(), 'Eeeeek!');
    }
}
