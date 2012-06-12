<?php

namespace Pheasant\Tests\Dsn;

use \Pheasant\Database\Dsn;

require_once(__DIR__.'/../vendor/lastcraft/simpletest/autorun.php');
require_once(__DIR__.'/base.php');

class DsnTestCase extends \Pheasant\Tests\MysqlTestCase
{
	public function testParsingADsn()
	{
		$dsn = new Dsn("mysqli://user:pass@hostname:3306/mydb");

		$this->assertEqual($dsn->scheme, 'mysqli');
		$this->assertEqual($dsn->user, 'user');
		$this->assertEqual($dsn->pass, 'pass');
		$this->assertEqual($dsn->host, 'hostname');
		$this->assertEqual($dsn->port, 3306);
		$this->assertEqual($dsn->database, 'mydb');
	}

	public function testBuildingADsn()
	{
		$dsn = new Dsn("mysqli://user:pass@hostname:3306/mydb");
		$dsn->host = 'anotherhost';
		$dsn->port = 3307;

		$this->assertEqual($dsn->__toString(), 'mysqli://user:pass@anotherhost:3307/mydb');
	}

	public function testBuildingADsnWithoutDb()
	{
		$dsn = new Dsn("mysqli://user:pass@hostname:3306");

		$this->assertFalse(isset($dsn->database));
		$this->assertEqual($dsn->__toString(), 'mysqli://user:pass@hostname:3306');
	}	

	public function testBuildingADsnWithQueryStrings()
	{
		$raw = "mysqli://user:pass@hostname:3306?myparam=llamas&another=blargh";
		$dsn = new Dsn($raw);

		$this->assertEqual($dsn->params['another'], 'blargh');
		$this->assertEqual($dsn->params['myparam'], 'llamas');
		$this->assertEqual($dsn->__toString(), $raw);
	}	
	
}
