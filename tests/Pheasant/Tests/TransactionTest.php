<?php

namespace Pheasant\Tests\Transaction;
use \Pheasant\Database\Mysqli\Transaction;
use \Pheasant\Tests\Examples\Animal;

class TransactionTest extends \Pheasant\Tests\MysqlTestCase
{
    public function setUp()
    {
        parent::setUp();

        $migrator = new \Pheasant\Migrate\Migrator();
        $migrator
            ->create('animal', Animal::schema())
            ;

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

        $this->assertEquals(count($this->queries), 2);
        $this->assertEquals($this->queries[0], 'BEGIN');
        $this->assertEquals($this->queries[1], 'ROLLBACK');
    }

    public function testCallbacksWithConnectionCalls()
    {
        $sql = "SELECT * FROM animal";
        $connection = $this->connection();

        $transaction = new Transaction($connection);
        $transaction->callback(function() use ($connection, $sql) {
            $connection->execute($sql);
        });

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

        $this->assertEquals(count($this->queries), 2);
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

        $this->assertEquals(count($this->queries), 2);
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

        try {
          $transaction->execute();
        } catch(\Exception $e) {
          $exception = $e;
        }

        $this->assertInstanceOf('\Exception', $exception);
        $this->assertEquals(count($this->queries), 2);
        $this->assertEquals($this->queries[0], 'BEGIN');
        $this->assertEquals($this->queries[1], 'ROLLBACK');
    }

    public function testNestedDeferEventsFireOnRollback()
    {
        $connection = $this->connection();

        $events = \Mockery::mock();
        $events->shouldReceive('cork')->once()->andReturn($events);
        $events->shouldReceive('discard')->once()->andReturn($events);
        $events->shouldReceive('uncork')->once()->andReturn($events);

        $transaction = new Transaction($connection);
        $transaction->deferEvents($events);
        $transaction->callback(function() use($connection){
            $t = new Transaction($connection);
            $t->callback(function() use($connection){
                $t = new Transaction($connection);
                $t->callback(function() use($connection){
                    throw new \Exception("Llamas :( :)");
                })->execute();
            })->execute();
        });

        try {
          $transaction->execute();
        } catch(\Exception $e) {
          $exception = $e;
        }

        $this->assertInstanceOf('\Exception', $exception);
        $this->assertEquals(count($this->queries), 6);
        $this->assertEquals($this->queries[0], 'BEGIN');
        $this->assertEquals($this->queries[1], 'SAVEPOINT savepoint_1');
        $this->assertEquals($this->queries[2], 'SAVEPOINT savepoint_2');
        $this->assertEquals($this->queries[3], 'ROLLBACK TO savepoint_2');
        $this->assertEquals($this->queries[4], 'ROLLBACK TO savepoint_1');
        $this->assertEquals($this->queries[5], 'ROLLBACK');
    }
}
