<?php

namespace Pheasant\Tests;

use \Pheasant;
use \Pheasant\DomainObject;
use \Pheasant\Types;
use \Pheasant\Tests\Examples\MyDomainObject;

class EventsTestCase extends \Pheasant\Tests\MysqlTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->mapper = \Mockery::mock('\Pheasant\Mapper\Mapper');

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
        $this->mapper->shouldReceive('save')->times(1);

        $events = array();
        $callback = function($e) use (&$events) { $events[]=$e; };

        $this->initialize('Pheasant\DomainObject', function($builder) use ($callback) {
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

        $this->assertEquals($do->test, "blargh");
        $this->assertEquals($events, array('afterCreate'));
    }

    public function testEventsBoundToObject()
    {
        $this->mapper->shouldReceive('save')->times(2);
        $events = array();

        $this->initialize('Pheasant\DomainObject', function($builder) {
            $builder->properties(array(
                'test' => new Types\String()
                ));
        });

        $do1 = new DomainObject();
        $do2 = new DomainObject();

        $do1->events(array(
            'afterSave'=>function($e) use (&$events) { $events[] = "do1.$e"; },
            ));

        $do2->events(array(
            'afterSave'=>function($e) use (&$events) { $events[] = "do2.$e"; },
            ));

        $do1->save();
        $do2->save();

        $this->assertEquals($events, array('do1.afterSave', 'do2.afterSave'));
    }

    public function testBuiltInEventMethods()
    {
        $this->mapper->shouldReceive('save')->times(1);

        $this->initialize('Pheasant\Tests\Examples\MyDomainObject', function($builder) {
            $builder->properties(array(
                'test' => new Types\String()
                ));
        });

        $do = new MyDomainObject();
        $do->test = 'Llamas';
        $do->save();

        $this->assertEquals($do->test, 'blargh');
    }
}
