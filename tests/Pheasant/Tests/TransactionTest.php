<?php

namespace Pheasant\Tests\Transaction;
use \Pheasant\Database\Mysqli\Transaction;

class TransactionTest extends \Pheasant\Tests\MysqlTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->queries = array();
        $test = $this;
        $this->connection()->filterChain()->onQuery(function($sql) use($test) {
            $test->queries []= $sql;
            return $sql;
        });
    }

    public function testBasicSuccessfulTransaction()
    {
        $connection = $this->connection();

        $transaction = new Transaction($connection);
        $transaction->callback(function(){
            return 'blargh';
        });

        $transaction->execute();
        $this->assertEquals(count($this->queries), 2);
        $this->assertEquals($this->queries[0], 'BEGIN');
        $this->assertEquals($this->queries[1], 'COMMIT');
        $this->assertEquals(count($transaction->results), 1);
        $this->assertEquals($transaction->results[0], 'blargh');
    }

    public function testExceptionsCauseRollback()
    {
        $connection = $this->connection();
        $transaction = new Transaction($connection);
        $transaction->callback(function(){
            throw new \Exception('Eeeek!');
        });

        $this->setExpectedException('\Exception');
        $transaction->execute();

        $this->assertEquals($this->queries[0], 'BEGIN');
        $this->assertEquals($this->queries[1], 'ROLLBACK');
    }

    public function testCallbacksWithConnectionCalls()
    {
        $sql = "SELECT * FROM 'table'";
        $connection = $this->connection();

        $transaction = new Transaction($connection);
        $transaction->callback(function() use ($connection, $sql) {
            $connection->execute($sql);
        });

        $this->setExpectedException('\Exception');
        $transaction->execute();

        $this->assertEquals(count($this->queries), 3);
        $this->assertEquals($this->queries[0], 'BEGIN');
        $this->assertEquals($this->queries[1], $sql);
        $this->assertEquals($this->queries[2], 'COMMIT');
    }

    public function testCallbacksWithParams()
    {
        $connection = $this->connection();

        $transaction = new Transaction($connection);
        $transaction->callback(function($param) {
            return $param;
        }, 'blargh');

        $transaction->execute();
        $this->assertEquals(count($transaction->results), 1);
        $this->assertEquals($transaction->results[0], 'blargh');

        $this->assertEquals($this->queries[0], 'BEGIN');
        $this->assertEquals($this->queries[1], 'COMMIT');
    }

    public function testDeferEventsFireOnCommit()
    {
        $connection = $this->connection();

        $events = \Mockery::mock();
        $events->shouldReceive('cork')->once();
        $events->shouldReceive('uncork')->once();

        $transaction = new Transaction($connection);
        $transaction->deferEvents($events);
        $transaction->callback(function(){
            return 'blargh';
        });

        $transaction->execute();
        $this->assertEquals($this->queries[0], 'BEGIN');
        $this->assertEquals($this->queries[1], 'COMMIT');
    }

    public function testDeferEventsFireOnRollback()
    {
        $connection = $this->connection();

        $events = \Mockery::mock();
        $events->shouldReceive('cork')->once()->andReturn($events);
        $events->shouldReceive('discard')->once()->andReturn($events);
        $events->shouldReceive('uncork')->once()->andReturn($events);

        $transaction = new Transaction($connection);
        $transaction->deferEvents($events);
        $transaction->callback(function(){
            throw new \Exception("Llamas :( :)");
        });

        $this->setExpectedException('\Exception');
        $transaction->execute();

        $this->assertEquals($this->queries[0], 'BEGIN');
        $this->assertEquals($this->queries[1], 'ROLLBACK');
    }
}
