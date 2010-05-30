<?php

namespace pheasant\tests\state;
use \pheasant\Memento;

require_once('autorun.php');
require_once(__DIR__.'/base.php');

class MementoTestCase extends \pheasant\tests\TestCase
{
	public function testSettingData()
	{
		$memento = new Memento(array(
			'test'=>'blargh',
			'testmore'=>'mlerm',
			));

		$this->assertEqual($memento->test, 'blargh');
		$this->assertEqual($memento->testmore, 'mlerm');
		$this->assertEqual($memento->revisionNumber(), 1);

		$memento->awesome = 'pteradactyls';

		$this->assertEqual($memento->test, 'blargh');
		$this->assertEqual($memento->awesome, 'pteradactyls');
		$this->assertEqual($memento->revisionNumber(), 2);
	}

	public function testChangesAfter()
	{
		$memento = new Memento();
		$memento->awesome = 'pteradactyls';

		$this->assertEqual($memento->revisionNumber(), 1);
		$this->assertEqual($memento->changesAfter(0), array(
			'awesome'
			));

		$memento->test = 'blargh';

		$this->assertEqual($memento->revisionNumber(), 2);
		$this->assertEqual($memento->changesAfter(1), array(
			'test'
			));
	}

	public function testUnset()
	{
		$memento = new Memento();
		$memento->test1 = 'pteradactyls';
		$memento->test2 = 'mlerm';
		$memento->test3 = 'blargh';
		unset($memento->test1);

		$this->assertEqual($memento->revisionNumber(), 4);
		$this->assertFalse(isset($memento->awesome1));

		// check the revision() method reflects the state
		$this->assertEqual($memento->revision(3), (object) array(
			'test1'=>'pteradactyls',
			'test2'=>'mlerm',
			'test3'=>'blargh',
			));

		$this->assertEqual($memento->revision(4), (object) array(
			'test2'=>'mlerm',
			'test3'=>'blargh',
			));

		$this->assertEqual($memento->changesAfter(3), array(
			'test1'
			));
	}
}
