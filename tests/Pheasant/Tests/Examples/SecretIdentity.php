<?php

namespace Pheasant\Tests\Examples;

use \Pheasant\DomainObject;
use \Pheasant\Types;
use \Pheasant\Types\SequenceType;
use \Pheasant\Types\StringType;

class SecretIdentity extends DomainObject
{
    public function properties()
    {
        return array(
            'id' => new Types\SequenceType(),
            'realname' => new Types\StringType(),
            );
    }

    public function relationships()
    {
        return array(
            'Hero' => Hero::hasOne('id', 'identityid')
            );
    }
}
