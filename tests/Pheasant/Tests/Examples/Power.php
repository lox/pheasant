<?php

namespace Pheasant\Tests\Examples;

use \Pheasant\DomainObject;
use \Pheasant\Types;
use \Pheasant\Types\SequenceType;
use \Pheasant\Types\StringType;
use \Pheasant\Types\IntegerType;

class Power extends DomainObject
{
    public function properties()
    {
        return array(
            'id' => new Types\SequenceType(),
            'description' => new Types\StringType(),
            'heroid' => new Types\IntegerType()
            );
    }

    public function relationships()
    {
        return array(
            'Hero' => Hero::belongsTo('heroid','id')
            );
    }
}
