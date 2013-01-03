<?php

namespace Pheasant\Tests\Examples;

use \Pheasant\DomainObject;
use \Pheasant\Types;

class AnotherAnimal extends DomainObject
{
    public function tableName()
    {
        return 'animal';
    }

    public function properties()
    {
        return array(
            'id' => new Types\Integer(11, 'primary auto_increment'),
            'type' => new Types\String(255, 'required default=llama'),
        );
    }
}
