<?php

namespace Pheasant\Tests;

use \Pheasant\Database\Binder;

class BindingTest extends \Pheasant\Tests\MysqlTestCase
{
    public function testBasicStringBinding()
    {
        $binder = new Binder();
        $this->assertEquals(
            $binder->bind('SELECT * FROM table WHERE column=?', array('test')),
            "SELECT * FROM table WHERE column='test'"
            );
    }

    public function testIntBinding()
    {
        $binder = new Binder();
        $this->assertEquals(
            $binder->bind('column=?', array(24)),
            "column='24'"
            );
    }

    public function testNullBinding()
    {
        $binder = new Binder();
        $this->assertEquals(
            $binder->magicBind('column=?', array(null)),
            'column IS NULL'
            );
    }

    public function testMultipleBinding()
    {
        $binder = new Binder();
        $this->assertEquals(
            $binder->magicBind('a=? and b=?', array(24, 'test')),
            "a='24' and b='test'"
            );
    }

    public function testArrayBinding()
    {
        $binder = new Binder();
        $this->assertEquals(
            $binder->magicBind('a=? and b=?', array(24, array(1, 2, "llama's"))),
            "a='24' and b IN ('1','2','llama\'s')"
            );
    }

    public function testEmptyArrayBinding()
    {
        $binder = new Binder();
        $this->assertEquals(
            $binder->magicBind('x=?', array(array())),
            'x IN (null)'
        );
    }

    public function testInjectingStatements()
    {
        $binder = new Binder();
        $this->assertEquals(
            $binder->bind('x=?', array('10\'; DROP TABLE --')),
            "x='10\'; DROP TABLE --'"
            );
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testBindMissingParameters()
    {
        $binder = new Binder();
        $binder->bind('x=? and y=?', array(24));
    }

    public function testBoolBinding()
    {
        $binder = new Binder();
        $this->assertEquals(
            $binder->bind('column1=? and column2=?', array(false, true)),
            "column1='' and column2=1"
        );
    }

    public function testBindIntoAQueryWithQuestionMarksInQuotes()
    {
        $binder = new Binder();

        $this->assertEquals(
            $binder->bind("name='???' and llamas=?", array(24)),
            "name='???' and llamas='24'"
        );
    }

    public function testBindIntoAQueryWithEscapedQuotesInStrings()
    {
        $binder = new Binder();

        $this->assertEquals(
            $binder->bind("name='\'7r' and llamas=?", array(24)),
            "name='\'7r' and llamas='24'"
        );

        $this->assertEquals(
            $binder->bind("name='\'7r\\\\' and another='test question?' and llamas=?", array(24)),
            "name='\'7r\\\\' and another='test question?' and llamas='24'"
        );

        $this->assertEquals(
            $binder->bind("name='\'7r\\\\' and x='\'7r' and llamas=?", array(24)),
            "name='\'7r\\\\' and x='\'7r' and llamas='24'"
        );
    }

    public function testBindIntoAQueryWithQuotesInQuotes()
    {
        $binder = new Binder();

        $this->assertEquals(
            $binder->bind("name='\"' and llamas=?", array(24)),
            "name='\"' and llamas='24'"
        );
    }

    public function testBindWithBackquote()
    {
        $binder = new Binder();
        $this->assertEquals(
            $binder->magicBind('`id`=?', array(1)),
            "`id`='1'"
        );
    }
}
