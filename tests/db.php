<?php

namespace Pheasant\Tests\Db;
use \Pheasant\Database\Binder;

require_once('autorun.php');
require_once(__DIR__.'/base.php');

class BindingTestCase extends \Pheasant\Tests\MysqlTestCase
{
	public function testBasicStringBinding()
	{
		$binder = new Binder();
		$this->assertEqual(
			$binder->bind('SELECT * FROM table WHERE column=?', 'test'),
			"SELECT * FROM table WHERE column='test'"
			);
	}

	public function testIntBinding()
	{
		$binder = new Binder();
		$this->assertEqual(
			$binder->bind('column=?', 24),
			"column='24'"
			);
	}

	public function testNullBinding()
	{
		$binder = new Binder();
		$this->assertEqual(
			$binder->bind('column=?', null),
			'column=NULL'
			);
	}

	public function testMultipleBinding()
	{
		$binder = new Binder();
		$this->assertEqual(
			$binder->bind('a=? and b=?', array(24, 'test')),
			"a='24' and b='test'"
			);
	}
}
