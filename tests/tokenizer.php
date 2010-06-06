<?php

namespace pheasant\tests\tokenizer;
use \pheasant\database\Tokenizer;

require_once('autorun.php');
require_once(__DIR__.'/base.php');

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
		$this->assertEqual($sql, implode('', $tokenizer->tokenize()));
	}
}
