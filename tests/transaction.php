<?php

namespace Pheasant\Tests\Transaction;
use \Pheasant\Database\Mysqli\Transaction;

require_once(__DIR__.'/../vendor/simpletest/autorun.php');
require_once(__DIR__.'/base.php');

class TransactionTestCase extends \Pheasant\Tests\DbTestCase
{
	public function testBasicSuccessfulTransaction()
	{
		$connection = new \MockConnection();
		$connection->expectAt(0,'execute',array('BEGIN'));
		$connection->expectAt(1,'execute',array('COMMIT'));

		$transaction = new Transaction($connection);
		$transaction->callback(function(){
			return 'blargh';
		});

		$transaction->execute();
		$this->assertEqual(count($transaction->results), 1);
		$this->assertEqual($transaction->results[0], 'blargh');
	}

	public function testExceptionsCauseRollback()
	{
		$connection = new \MockConnection();
		$connection->expectAt(0,'execute',array('BEGIN'));
		$connection->expectAt(1,'execute',array('ROLLBACK'));

		$transaction = new Transaction($connection);
		$transaction->callback(function(){
			throw new \Exception('Eeeek!');
		});

		try
		{
			$transaction->execute();
			$this->fail("exception should have been thrown");
		}
		catch(\Exception $e) {}
	}

	public function testCallbacksWithConnectionCalls()
	{
		$sql = "SELECT * FROM table";
		$connection = new \MockConnection();
		$connection->expectAt(0,'execute',array('BEGIN'));
		$connection->expectAt(1,'execute',array($sql));
		$connection->expectAt(2,'execute',array('COMMIT'));

		$transaction = new Transaction($connection);
		$transaction->callback(function() use($connection, $sql) {
			$connection->execute($sql);
		});

		$transaction->execute();
	}

	public function testCallbacksWithParams()
	{
		$connection = new \MockConnection();
		$connection->expectAt(0,'execute',array('BEGIN'));
		$connection->expectAt(2,'execute',array('COMMIT'));

		$transaction = new Transaction($connection);
		$transaction->callback(function($param) {
			return $param;
		}, 'blargh');

		$transaction->execute();
		$this->assertEqual(count($transaction->results), 1);
		$this->assertEqual($transaction->results[0], 'blargh');
	}
}
