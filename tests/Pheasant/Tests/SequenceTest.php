<?php

namespace Pheasant\Tests\Sequences;

use \Pheasant\Mapper\RowMapper;
use \Pheasant\Database\Mysqli\SequencePool;
use \Pheasant\DomainObject;
use \Pheasant\Types\Sequence;
use \Pheasant\Types\String;

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
