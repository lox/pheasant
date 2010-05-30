<?php

namespace pheasant\tests\db;
use \pheasant\database\mysqli\Binder;
use \pheasant\database\Tokenizer;

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

class TokenizerTestCase extends \UnitTestCase
{
	public function testTokenizingSelect()
	{
		$sql = "select mytable.test from `table` as mytable
			inner join fred using(id) where x like '%test'";

		$tokenizer = new Tokenizer($sql);
		$this->assertEqual($sql, implode('', $tokenizer->tokenize()));
	}

	public function testTokenizingSubquery()
	{
		$sql = "select test from `table`
			where x in (select id from subtable) and col = 'some complicated \\\' `string`'";

		$tokenizer = new Tokenizer($sql);
		$this->assertEqual($sql, implode('', $tokenizer->tokenize()));
	}

	public function testTokenizingBinds()
	{
		$sql = "select * from table where x = ?";;

		$tokenizer = new Tokenizer($sql);
		var_dump($tokenizer->tokenize());
		$this->assertEqual($sql, implode('', $tokenizer->tokenize()));
	}
}
