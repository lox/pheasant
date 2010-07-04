<?php

namespace pheasant\tests\criteria;

use \Pheasant;
use pheasant\query\Criteria;

require_once('autorun.php');
require_once(__DIR__.'/base.php');

class CriteriaTestCase extends \pheasant\tests\MysqlTestCase
{
	public function testBasicCriteria()
	{
		$criteria = new Criteria('?', 'test');
		$this->assertEqual("'test'", $criteria->toSql());

		$criteria = new Criteria('column > ?', 55);
		$this->assertEqual("column > 55", $criteria->toSql());

		$criteria = new Criteria(55);
		$this->assertEqual("55", $criteria->toSql());
	}

	public function testNestedCriteria()
	{
		$cr = new Criteria();
		$cr->or(
			$cr->and('a > 1', $cr->bind('b != ?', 'blargh')),
			'x = 1'
			);

		$this->assertEqual("((a > 1 AND b != 'blargh') OR x = 1)",
			$cr->toSql());
	}
}
