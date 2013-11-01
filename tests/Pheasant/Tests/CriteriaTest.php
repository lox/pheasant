<?php

namespace Pheasant\Tests;

use \Pheasant;
use \Pheasant\Query\Criteria;

class CriteriaTest extends \Pheasant\Tests\MysqlTestCase
{
    public function testBasicCriteria()
    {
        $c = new Criteria('column = ?', 'test');
        $this->assertEquals("(column = 'test')", $c->toSql());

        $c = new Criteria('`column` = ?', array(array('a', 'b')));
        $this->assertEquals("(`column` IN ('a','b'))", $c->toSql());

        $c = new Criteria('column > ?', 55);
        $this->assertEquals("(column > '55')", $c->toSql());
    }

    public function testCriteriaFromArray()
    {
        $c = new Criteria(array('key1' => 'val1', 'key2' => 'val2'));
        $this->assertEquals("(`key1`='val1' AND `key2`='val2')", $c->toSql());
    }

    public function testCriteriaConcatWithAnd()
    {
        $c = new Criteria('column > ?', 55);
        $c->and('column <> 0')->and('column < 100');

        $this->assertEquals("(((column > '55') AND column <> 0) AND column < 100)", $c->toSql());
    }

    public function testAddingToCriteriaWithOr()
    {
        $c = new Criteria('column > ?', 55);
        $c->or('column <> 0', 'column < 100')->and('column < 30');

        $this->assertEquals("(((column > '55') OR column <> 0 OR column < 100) AND column < 30)", $c->toSql());
    }

    public function testCriteriaConcat()
    {
        $c = new Criteria();
        $c = $c->or(
            Criteria::concatAnd('a > 1', $c->bind('b != ?', 'blargh')),
            'x = 1'
            );

        $this->assertEquals("((a > 1 AND b != 'blargh') OR x = 1)",
            $c->toSql());
    }
}
