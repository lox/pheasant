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

	public function testBoolBinding()
	{
		$binder = new Binder();
		$this->assertEqual(
			$binder->bind('column1=? and column2=?', array(false, true)),
			"column1='' and column2=1"
		);
	}

	public function testBindIntoAQueryWithQuestionMarksInQuotes()
	{
		$binder = new Binder();

		$this->assertEqual(
			$binder->bind("name='???' and llamas=?", array(24)),
			"name='???' and llamas='24'"
		);
	}

	public function testBindIntoAQueryWithEscapedQuotesInStrings()
	{
		$binder = new Binder();

		$this->assertEqual(
			$binder->bind("name='\'7r' and llamas=?", array(24)),
			"name='\'7r' and llamas='24'"
		);

		$this->assertEqual(
			$binder->bind("name='\'7r\\\\' and llamas=?", array(24)),
			"name='\'7r\\\\' and llamas='24'"
		);

		$this->assertEqual(
			$binder->bind("name='\'7r\\\\' and x='\'7r' and llamas=?", array(24)),
			"name='\'7r\\\\' and x='\'7r' and llamas='24'"
		);
	}

	public function testBindIntoAQueryWithQuotesInQuotes()
	{
		$binder = new Binder();

		$this->assertEqual(
			$binder->bind("name='\"' and llamas=?", array(24)),
			"name='\"' and llamas='24'"
		);
	}

	public function testBindIntoAQueryFailsWithUnmatchedQuotes()
	{
		$this->expectException('\Pheasant\Database\Exception');
		$binder = new Binder();
		$binder->bind("name=' and llamas=?", array(24));
	}
}
