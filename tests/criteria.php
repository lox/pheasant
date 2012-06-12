<?php

namespace Pheasant\Tests\Criteria;
use \Pheasant;
use \Pheasant\Query\Criteria;

require_once(__DIR__.'/../vendor/lastcraft/simpletest/autorun.php');
require_once(__DIR__.'/base.php');

class CriteriaTestCase extends \Pheasant\Tests\MysqlTestCase
{
	public function testBasicCriteria()
	{
		$criteria = new Criteria('?', array('test'));
		$this->assertEqual("'test'", $criteria->toSql());

		$criteria = new Criteria('column > ?', array(55));
		$this->assertEqual("column > '55'", $criteria->toSql());

		$criteria = new Criteria(55);
		$this->assertEqual("55", $criteria->toSql());
	}

	public function testNestedCriteria()
	{
		$cr = new Criteria();
		$cr->or(
			$cr->and('a > 1', $cr->bind('b != ?', array('blargh'))),
			'x = 1'
			);

		$this->assertEqual("((a > 1 AND b != 'blargh') OR x = 1)",
			$cr->toSql());
	}
}
