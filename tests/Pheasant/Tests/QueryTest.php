<?php

namespace Pheasant\Tests;

use \Pheasant;
use \Pheasant\Query\Query;

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
            "SELECT * FROM user INNER JOIN `mytable` using(tableid) WHERE (userid='55')",
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

        $query = new Query();
        $query
            ->select('foo')
            ->from('bar')
            ->orderBy('baz')
            ->andOrderBy('moo')
            ;

        $this->assertEquals(
            'SELECT foo FROM bar ORDER BY baz, moo',
            $query->toSql());
    }

    public function testChainedOrderByReplacesOrderBy()
    {
        $query = new Query();
        $query
            ->select('first_name, last_name')
            ->from('users')
            ->orderBy('last_name ASC')
            ->orderBy('first_name')
            ;

        $this->assertEquals(
            'SELECT first_name, last_name FROM users ORDER BY first_name',
            $query->toSql());
    }

    public function testAddingDefaultLock()
    {
        $query = new Query();
        $query
            ->select('userid')
            ->from('user')
            ->lock()
            ;

        $this->assertEquals(
            'SELECT userid FROM user FOR UPDATE',
            $query->toSql());
    }

    public function testAddingLockWithClause()
    {
        $query = new Query();
        $query
            ->select('userid')
            ->from('user')
            ->lock('LOCK IN SHARE MODE')
            ;

        $this->assertEquals(
            'SELECT userid FROM user LOCK IN SHARE MODE',
            $query->toSql());
    }

    public function testDistinctColumn()
    {
        $query = new Query();
        $query
            ->distinct()
            ->select('userid')
            ->from('user')
            ;

        $this->assertEquals(
            'SELECT DISTINCT userid FROM user',
            $query->toSql());
    }

    public function testCount()
    {
        $query = new Query();
        $query
            ->select('firstname')
            ->from('user')
            ->where('lastname=?','Castle')
            ;
        $this->assertSame(1, $query->count());
    }
}
