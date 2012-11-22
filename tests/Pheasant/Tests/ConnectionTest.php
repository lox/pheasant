<?php

namespace Pheasant\Tests;

use \Pheasant;
use \Pheasant\Database\Mysqli;
use \Pheasant\Database\Dsn;

class ConnectionTest extends \PHPUnit_Framework_TestCase
{
  public function setUp()
  {
    $this->dsn = new Dsn('mysql://root@localhost/pheasanttest?charset=utf8');
    $this->conn = new Mysqli\Connection($this->dsn);
  }

  public function testConnecting()
  {
    $this->assertTrue(is_numeric($this->conn->execute("SELECT CONNECTION_ID()")->scalar()));
  }

  public function testReconnecting()
  {
    $id = $this->conn->execute("SELECT CONNECTION_ID()")->scalar();

    // force a re-connect
    $this->conn->connect();
    $this->assertNotEquals($id, $this->conn->execute("SELECT CONNECTION_ID()")->scalar());
  }

  public function testSelectedDatabase()
  {
    $this->assertEquals("pheasanttest", $this->conn->selectedDatabase());
  }
}

