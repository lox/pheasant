<?php

namespace Pheasant\Tests\Mocks;

use \Pheasant;
use \Pheasant\DomainObject;
use \Pheasant\Types;

require_once(__DIR__.'/../vendor/simpletest/autorun.php');
require_once(__DIR__.'/base.php');

\Mock::generate('\Pheasant\DomainObject','MockDomainObject');

class MockTestCase extends \Pheasant\Tests\MysqlTestCase
{
	public function testCreatingAMock()
	{
		Pheasant::instance()->mock('Pheasant\Tests\Mocks\NotCreatedYet', function() {
			return new \MockDomainObject();
		});

		$mock = new NotCreatedYet();
		$this->assertIsA($mock, 'Pheasant\MockProxy');

		$mock->setReturnValue('toArray', array('llamas'=>true));
		$this->assertEqual($mock->toArray(), array('llamas'=>true));

		$mock2 = new NotCreatedYet();
		$this->assertFalse($mock === $mock2);
	}
}


