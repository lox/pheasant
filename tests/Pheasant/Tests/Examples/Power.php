<?php

namespace Pheasant\Tests\Examples;

use \Pheasant\DomainObject;
use \Pheasant\Types;
use \Pheasant\Types\Sequence;
use \Pheasant\Types\String;

class Power extends DomainObject
{
    public function properties()
    {
        return array(
            'id' => new Types\Sequence(),
            'description' => new Types\String(),
            'heroid' => new Types\Integer()
            );
    }

    public function relationships()
    {
        return array(
            'Hero' => Hero::belongsTo('heroid','id')
            );
    }
}
