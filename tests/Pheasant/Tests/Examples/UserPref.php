<?php

namespace Pheasant\Tests\Examples;

use \Pheasant\DomainObject;
use \Pheasant\Types;
use \Pheasant\Types\String;

class UserPref extends DomainObject
{
    public function properties()
    {
        return array(
            'userid' => new Types\Integer(13, 'primary'),
            'pref' => new Types\String(),
            'value' => new Types\String(),
            );
    }

    public function relationships()
    {
        return array(
            'User' => User::belongsTo('userid')
            );
    }
}
