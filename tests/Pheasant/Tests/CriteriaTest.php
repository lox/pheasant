<?php

namespace Pheasant\Tests;

use \Pheasant;
use \Pheasant\Query\Criteria;

class CriteriaTest extends \Pheasant\Tests\MysqlTestCase
{
    public function testBasicCriteria()
    {
        $criteria = new Criteria('?', array('test'));
        $this->assertEquals("'test'", $criteria->toSql());

        $criteria = new Criteria('column > ?', array(55));
        $this->assertEquals("column > '55'", $criteria->toSql());

        $criteria = new Criteria(55);
        $this->assertEquals("55", $criteria->toSql());

        $criteria = new Criteria(array('key1' => 'val1', 'key2' => 'val2'));
        $this->assertEquals("(`key1`='val1' AND `key2`='val2')", $criteria->toSql());
    }

    public function testNestedCriteria()
    {
        $cr = new Criteria();
        $cr->or(
            $cr->and('a > 1', $cr->bind('b != ?', array('blargh'))),
            'x = 1'
            );

        $this->assertEquals("((a > 1 AND b != 'blargh') OR x = 1)",
            $cr->toSql());
    }
}
