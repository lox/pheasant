<?php

namespace Pheasant\Tests\Sequences;

use \Pheasant\Database\Mysqli\SequencePool;

class SequenceTestCase extends \Pheasant\Tests\MysqlTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->pool = new SequencePool($this->pheasant->connection());
        $this->pool
            ->initialize()
            ->clear()
            ;

        $this->assertTableExists(SequencePool::TABLE);
    }

    public function testSequences()
    {
        $this->assertEquals(1, $this->pool->next('my_sequence'));
        $this->assertEquals(2, $this->pool->next('my_sequence'));
        $this->assertEquals(3, $this->pool->next('my_sequence'));
        $this->assertEquals(4, $this->pool->next('my_sequence'));
    }
}
