<?php

namespace pheasant\tests\db;
use \pheasant\database\mysqli\Binder;

require_once('autorun.php');
require_once(__DIR__.'/base.php');

class BindingTestCase extends \pheasant\tests\MysqlTestCase
{
	public function testBasicStringBinding()
	{
		$binder = new Binder("SELECT * FROM table WHERE column=?", array(
			'test'
			));

		$this->assertEqual($binder->__toString(),
			"SELECT * FROM table WHERE column='test'");
	}

	public function testIntBinding()
	{
		$binder = new Binder("column=?", array(24));
		$this->assertEqual($binder->__toString(), 'column=24');
	}

	public function testNullBinding()
	{
		$binder = new Binder("column=?", array(null));
		$this->assertEqual($binder->__toString(), 'column=NULL');
	}
}

