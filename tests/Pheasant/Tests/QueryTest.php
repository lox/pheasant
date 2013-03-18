<?php

namespace Pheasant\Tests;

use \Pheasant;
use \Pheasant\Types\Integer;
use \Pheasant\Types\String;
use \Pheasant\Query\Query;
use \Pheasant\Query\Criteria;

class QueryTest extends \Pheasant\Tests\MysqlTestCase
{
	public function testQuerying()
	{
		$query = new Query();
		$query
			->select('firstname')
			->from('user')
			->where('lastname=?','Castle')
			;

		$this->assertEquals(
			"SELECT firstname FROM user WHERE (lastname='Castle')",
			$query->toSql()
		);
	}

	public function testAddingWhereClauses()
	{
		$query = new Query();
		$query
			->select('firstname')
			->from('user')
			->where('lastname=?','Castle')
			->andWhere('firstname=?','Frank')
			;

		$this->assertEquals(
			"SELECT firstname FROM user WHERE ((lastname='Castle') AND (firstname='Frank'))",
			$query->toSql()
		);
	}

	public function testJoiningMultipleQueryObjects()
	{
		// outer query
		$query = new Query();
		$query
			->from('user')
			->innerJoin('mytable', 'using(tableid)')
			->where('userid=?',55)
			;

		$this->assertEquals(
			"SELECT * FROM user INNER JOIN mytable using(tableid) WHERE (userid='55')",
			$query->toSql()
			);
	}

	public function testInnerJoinOnObjects()
	{
		// inner query
		$innerQuery = new Query();
		$innerQuery
			->select('groupname', 'groupid')
			->from('group')
			;

		// outer query
		$query = new Query();
		$query
			->select('firstname')
			->from('user')
			->innerJoin($innerQuery, 'USING(groupid)')
			->where('lastname=?','Castle')
			;

		$innerQuery
			->where('derived.firstname = ?', 'frank');

		$this->assertEquals('SELECT firstname FROM user '.
			'INNER JOIN (SELECT groupname, groupid FROM group) derived USING(groupid) '.
			'WHERE (lastname=\'Castle\')',
			$query->toSql()
			);
	}

	public function testAddingGroupBy()
	{
		$query = new Query();
		$query
			->select('userid')
			->from('user')
			->groupBy('userid')
			;

		$this->assertEquals(
			'SELECT userid FROM user GROUP BY userid',
			$query->toSql());
	}

	public function testAddingOrderBy()
	{
		$query = new Query();
		$query
			->select('userid')
			->from('user')
			->orderBy('userid')
			;

		$this->assertEquals(
			'SELECT userid FROM user ORDER BY userid',
			$query->toSql());
	}
}

