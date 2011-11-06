<?php

namespace Pheasant\Tests\Events;

use \Pheasant;
use \Pheasant\DomainObject;
use \Pheasant\Types;

require_once(__DIR__.'/../vendor/simpletest/autorun.php');
require_once(__DIR__.'/base.php');

\Mock::generate('\Pheasant\Mapper\Mapper','MockMapper');

class MyDomainObject extends DomainObject
{
	public function afterSave()
	{
		$this->test = 'blargh';
	}
}

class EventsTestCase extends \Pheasant\Tests\DbTestCase
{
	public function setUp()
	{
		$this->mapper = new \MockMapper();
	}

	/**
	 * Initialize DomainObject
	 */
	public function initialize($class, $callback=null)
	{
		Pheasant::instance()
			->register($class, $this->mapper)
			->initialize($class, $callback)
			;
	}

	public function testEventsBoundToSchema()
	{
		$events = array();
		$callback = function($e) use(&$events) { $events[]=$e; };

		$this->initialize('Pheasant\DomainObject', function($builder) use($callback) {
			$builder->properties(array(
				'test' => new Types\String()
				));
			$builder->events(array(
				'afterCreate' => $callback,
				));
		});

		$do = new DomainObject();
		$do->test = "blargh";
		$do->save();

		$this->assertEqual($do->test, "blargh");
		$this->assertEqual($events, array('afterCreate'));
	}

	public function testEventsBoundToObject()
	{
		$events = array();

		$this->initialize('Pheasant\DomainObject', function($builder) {
			$builder->properties(array(
				'test' => new Types\String()
				));
		});

		$do1 = new DomainObject();
		$do2 = new DomainObject();

		$do1->events(array(
			'afterSave'=>function($e) use(&$events) { $events[] = "do1.$e"; },
			));

		$do2->events(array(
			'afterSave'=>function($e) use(&$events) { $events[] = "do2.$e"; },
			));

		$do1->save();
		$do2->save();

		$this->assertEqual($events, array('do1.afterSave', 'do2.afterSave'));
	}

	public function testBuiltInEventMethods()
	{
		$this->initialize('Pheasant\Tests\Events\MyDomainObject', function($builder) {
			$builder->properties(array(
				'test' => new Types\String()
				));
		});

		$do = new MyDomainObject();
		$do->test = 'Llamas';
		$do->save();

		$this->assertEqual($do->test, 'blargh');
	}
}
