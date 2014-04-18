<?php

namespace Pheasant\Tests\Examples;

use \Pheasant\DomainObject;
use \Pheasant\Types;
use \Pheasant\Types\Sequence;
use \Pheasant\Types\String;

class SecretIdentity extends DomainObject
{
    public function properties()
    {
        return array(
            'id' => new Types\Sequence(),
            'realname' => new Types\String(),
            );
    }

    public function relationships()
    {
        return array(
            'Hero' => Hero::hasOne('id', 'identityid')
            );
    }
}
