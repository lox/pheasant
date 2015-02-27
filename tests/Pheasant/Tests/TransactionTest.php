<?php

namespace Pheasant\Tests\Transaction;
use \Pheasant\Database\Mysqli\Transaction;

class TransactionTest extends \Pheasant\Tests\MysqlTestCase
{
    public function testBasicSuccessfulTransaction()
    {
        $connection = \Mockery::mock('\Pheasant\Database\Mysqli\Connection');
        $connection->shouldReceive('execute')->with('BEGIN')->once();
        $connection->shouldReceive('execute')->with('COMMIT')->once();

        $transaction = new Transaction($connection);
        $transaction->callback(function(){
            return 'blargh';
        });

        $transaction->execute();
        $this->assertEquals(count($transaction->results), 1);
        $this->assertEquals($transaction->results[0], 'blargh');
    }

    public function testExceptionsCauseRollback()
    {
        $connection = \Mockery::mock('\Pheasant\Database\Mysqli\Connection');
        $connection->shouldReceive('execute')->with('BEGIN')->once();
        $connection->shouldReceive('execute')->with('ROLLBACK')->once();

        $transaction = new Transaction($connection);
        $transaction->callback(function(){
            throw new \Exception('Eeeek!');
        });

        $this->setExpectedException('\Exception');
        $transaction->execute();
    }

    public function testCallbacksWithConnectionCalls()
    {
        $sql = "SELECT * FROM table";
        $connection = \Mockery::mock('\Pheasant\Database\Mysqli\Connection');
        $connection->shouldReceive('execute')->with('BEGIN')->once();
        $connection->shouldReceive('execute')->with($sql)->once();
        $connection->shouldReceive('execute')->with('COMMIT')->once();

        $transaction = new Transaction($connection);
        $transaction->callback(function() use ($connection, $sql) {
            $connection->execute($sql);
        });

        $transaction->execute();
    }

    public function testCallbacksWithParams()
    {
        $connection = \Mockery::mock('\Pheasant\Database\Mysqli\Connection');
        $connection->shouldReceive('execute')->with('BEGIN')->once();
        $connection->shouldReceive('execute')->with('COMMIT')->once();

        $transaction = new Transaction($connection);
        $transaction->callback(function($param) {
            return $param;
        }, 'blargh');

        $transaction->execute();
        $this->assertEquals(count($transaction->results), 1);
        $this->assertEquals($transaction->results[0], 'blargh');
    }

    public function testDeferEventsFireOnCommit()
    {
        $connection = \Mockery::mock('\Pheasant\Database\Mysqli\Connection');
        $connection->shouldReceive('execute')->with('BEGIN')->once();
        $connection->shouldReceive('execute')->with('COMMIT')->once();

        $events = \Mockery::mock();
        $events->shouldReceive('cork')->once();
        $events->shouldReceive('uncork')->once();

        $transaction = new Transaction($connection);
        $transaction->deferEvents($events);
        $transaction->callback(function(){
            return 'blargh';
        });

        $transaction->execute();
    }

    public function testDeferEventsFireOnRollback()
    {
        $connection = \Mockery::mock('\Pheasant\Database\Mysqli\Connection');
        $connection->shouldReceive('execute')->with('BEGIN')->once();
        $connection->shouldReceive('execute')->with('ROLLBACK')->once();

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
    }
}
