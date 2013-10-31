<?php

namespace Pheasant\Tests\Examples;

use \Pheasant\DomainObject;
use \Pheasant\Types;
use \Pheasant\Types\Sequence;
use \Pheasant\Types\String;

class User extends DomainObject
{
    public function properties()
    {
        return array(
            'userid' => new Types\Sequence(),
            'firstname' => new Types\String(),
            'lastname' => new Types\String(),
            'group' => new Types\String(),
            );
    }

    public function relationships()
    {
        return array(
            'UserPrefs' => UserPref::hasMany('userid'),
            );
    }
}
