<?php

namespace Pheasant\Tests\Events;

use \Pheasant;
use \Pheasant\DomainObject;
use \Pheasant\Types;

require_once('autorun.php');
require_once(__DIR__.'/base.php');

\Mock::generate('\Pheasant\Mapper\Mapper','MockMapper');

class EventsTestCase extends \Pheasant\Tests\MysqlTestCase
{
    public function setUp()
    {
        $this->mapper = new \MockMapper(); 
        
        Pheasant::instance()
            ->register('Pheasant\DomainObject', $this->mapper)
            ->initialize('Pheasant\DomainObject', function($builder, $pheasant) {
                $builder->properties(array(
                    'test' => new Types\String()
                    ));
            });
    }

    public function testBasicEvents()
    {
        $do = new DomainObject();    
        $do->test = "blargh";
        $do->save();
	}
}

