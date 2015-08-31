<?php

namespace Pheasant\Tests\Examples;

use \Pheasant\DomainObject;
use \Pheasant\Types;
use \Pheasant\Types\SequenceType;
use \Pheasant\Types\StringType;

class User extends DomainObject
{
    public function properties()
    {
        return array(
            'userid' => new Types\SequenceType(),
            'firstname' => new Types\StringType(),
            'lastname' => new Types\StringType(),
            'group' => new Types\StringType(),
            );
    }

    public function relationships()
    {
        return array(
            'UserPrefs' => UserPref::hasMany('userid'),
            );
    }
}
