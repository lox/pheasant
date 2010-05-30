<?php

namespace pheasant\tests\sequences;
use \pheasant\database\mysqli\SequencePool;

require_once('autorun.php');
require_once(__DIR__.'/base.php');

class SequenceTestCase extends \pheasant\tests\MysqlTestCase
{
	public function setUp()
	{
		$this->pool = new SequencePool($this->connection());
		$this->pool
			->initialize()
			->clear()
			;
	}

	public function testSequences()
	{
		$this->assertEqual(1, $this->pool->next('my_sequence'));
		$this->assertEqual(2, $this->pool->next('my_sequence'));
		$this->assertEqual(3, $this->pool->next('my_sequence'));
		$this->assertEqual(4, $this->pool->next('my_sequence'));
	}
}
