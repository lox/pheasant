<?php

namespace Pheasant\Tests\Db;
use \Pheasant\Database\Binder;

require_once(__DIR__.'/../vendor/simpletest/autorun.php');
require_once(__DIR__.'/base.php');

class BindingTestCase extends \Pheasant\Tests\MysqlTestCase
{
	public function testBasicStringBinding()
	{
		$binder = new Binder();
		$this->assertEqual(
			$binder->bind('SELECT * FROM table WHERE column=?', array('test')),
			"SELECT * FROM table WHERE column='test'"
			);
	}

	public function testIntBinding()
	{
		$binder = new Binder();
		$this->assertEqual(
			$binder->bind('column=?', array(24)),
			"column='24'"
			);
	}

	public function testNullBinding()
	{
		$binder = new Binder();
		$this->assertEqual(
			$binder->magicBind('column=?', array(null)),
			'column IS NULL'
			);
	}

	public function testMultipleBinding()
	{
		$binder = new Binder();
		$this->assertEqual(
			$binder->magicBind('a=? and b=?', array(24, 'test')),
			"a='24' and b='test'"
			);
	}

	public function testArrayBinding()
	{
		$binder = new Binder();
		$this->assertEqual(
			$binder->magicBind('a=? and b=?', array(24, array(1, 2, "llama's"))),
			"a='24' and b IN ('1','2','llama\'s')"
			);
	}

	public function testInjectingStatements()
	{
		$binder = new Binder();
		$this->assertEqual(
			$binder->bind('x=?', array('10\'; DROP TABLE --')),
			"x='10\'; DROP TABLE --'"
			);
	}

	public function testBindMissingParameters()
	{
		$this->expectException('\InvalidArgumentException');

		$binder = new Binder();
		$binder->bind('x=? and y=?', array(24));
	}	
}
