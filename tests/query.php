<?php

namespace Pheasant\Tests\Query;
use \Pheasant;
use \Pheasant\Types\Integer;
use \Pheasant\Types\String;
use \Pheasant\Query\Query;
use \Pheasant\Query\Criteria;

require_once(__DIR__.'/../vendor/simpletest/autorun.php');
require_once(__DIR__.'/base.php');

class QueryTestCase extends \Pheasant\Tests\MysqlTestCase
{
	public function setUp()
	{
		$table =$this->table('user', array(
			'userid'=>new Integer(8, 'primary auto_increment'),
			'firstname'=>new String(),
			'lastname'=>new String(),
			));

		// create some users
		$table->insert(array('userid'=>null,'firstname'=>'Frank','lastname'=>'Castle'));
		$table->insert(array('userid'=>null,'firstname'=>'Cletus','lastname'=>'Kasady'));

	}

	public function testQuerying()
	{
		$query = new Query();
		$query
			->select('firstname')
			->from('user')
			->where('lastname=?','Castle')
			;

		$this->assertEqual(1, $query->count());
		$this->assertEqual(1, $query->execute()->count());
		$this->assertEqual(array('firstname'=>'Frank'), $query->execute()->offsetGet(0));
	}

	public function testJoins()
	{
		// outer query
		$query = new Query();
		$query
			->from('user')
			->innerJoin('mytable', 'using(tableid)')
			->where('userid=?',55)
			;

		$this->assertEqual('SELECT * FROM user '.
			'INNER JOIN mytable using(tableid) '.
			"WHERE userid='55'",
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

		$this->assertEqual('SELECT firstname FROM user '.
			'INNER JOIN (SELECT groupname, groupid FROM group) derived USING(groupid) '.
			'WHERE lastname=\'Castle\'',
			$query->toSql()
			);
	}
}

