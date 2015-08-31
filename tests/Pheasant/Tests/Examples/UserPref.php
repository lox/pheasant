<?php

namespace Pheasant\Tests\Examples;

use \Pheasant\DomainObject;
use \Pheasant\Types;
use \Pheasant\Types\StringType;

class UserPref extends DomainObject
{
    public function properties()
    {
        return array(
            'userid' => new Types\IntegerType(13, 'primary'),
            'pref' => new Types\StringType(),
            'value' => new Types\StringType(),
            );
    }

    public function relationships()
    {
        return array(
            'User' => User::belongsTo('userid')
            );
    }
}
