<?php

namespace Pheasant\Tests\Examples;

use \Pheasant\DomainObject;
use \Pheasant\Types;
use \Pheasant\Types\Sequence;

class Order extends DomainObject
{
    public function properties()
    {
        return array(
            'id' => new Types\Sequence(),
            );
    }
}
