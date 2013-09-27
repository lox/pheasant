<?php

namespace Pheasant\Tests\Examples;

use \Pheasant\DomainObject;

class EventTestObject extends DomainObject
{
    public $events = array();

    public function afterSave()
    {
        $this->events []= __FUNCTION__;
    }

    public function beforeSave()
    {
        $this->events []= __FUNCTION__;
    }

    public function onHydrate()
    {
        $this->events []= __FUNCTION__;
    }
}
