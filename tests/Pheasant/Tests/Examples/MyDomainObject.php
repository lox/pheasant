<?php

namespace Pheasant\Tests\Examples;

use \Pheasant\DomainObject;

class MyDomainObject extends DomainObject
{
    public function afterSave()
    {
        $this->test = 'blargh';
    }
}
