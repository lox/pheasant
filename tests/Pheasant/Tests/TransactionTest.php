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

        try {
            $transaction->execute();
            $this->fail("exception should have been thrown");
        } catch (\Exception $e) {}
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
}
