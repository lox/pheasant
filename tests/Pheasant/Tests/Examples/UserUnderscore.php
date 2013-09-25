<?php

namespace Pheasant\Tests\Examples;

use \Pheasant\DomainObject;
use \Pheasant\Types;
use \Pheasant\Types\Sequence;
use \Pheasant\Types\String;

class UserUnderscore extends DomainObject
{
    public function properties()
    {
        return array(
            'user_id' => new Types\Sequence(),
            'first_name' => new Types\String(),
            'last_name' => new Types\String(),
            );
    }

    public function relationships()
    {
        return array(
            'UserUnderscorePrefs' => UserPref::hasMany('user_id'),
            );
    }
}

