<?php

namespace Pheasant\Tests\FilterChain;

use \Pheasant\Database\FilterChain;

require_once(__DIR__.'/../vendor/simpletest/autorun.php');
require_once(__DIR__.'/base.php');

class FilterChainTestCase extends \Pheasant\Tests\MysqlTestCase
{
	public function testFilteringQuery()
	{
		$connection = new \MockConnection();
		$filter = new FilterChain();

		$filter->onQuery(function($sql) {
			return 'SELECT llamas FROM animals';
		});

		$connection->expectAt(0, 'execute', array('SELECT llamas FROM animals'));
		$filter->execute('SELECT 1', function($sql) use($connection) {
			$connection->execute($sql);
		});
	}

	public function testCatchingErrors()
	{
		$connection = new \MockConnection();
		$filter = new FilterChain();
		$exceptions = array();

		$filter->onError(function($e) use(&$exceptions) {
			$exceptions []= $e;
		});

		$connection->throwOn('execute', new \Exception('Eeeeek!'));

		try
		{
			$filter->execute('SELECT 1', function($sql) use($connection) {
				$connection->execute($sql);
			});

			$this->fail('Exception expected');
		}
		catch(\Exception $e)
		{
			$this->assertEqual($e->getMessage(), 'Eeeeek!');
		}

		$this->assertEqual(count($exceptions), 1);
		$this->assertEqual($exceptions[0]->getMessage(), 'Eeeeek!');
	}
}

