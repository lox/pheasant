<?php

namespace Pheasant\Tests\Examples;

use \Pheasant\DomainObject;
use \Pheasant\Types;
use \Pheasant\Types\SequenceType;
use \Pheasant\Types\StringType;
use \Pheasant\Types\IntegerType;

class Hero extends DomainObject
{
    public function properties()
    {
        return array(
            'id' => new Types\SequenceType(),
            'alias' => new Types\StringType(),
            'identityid' => new Types\IntegerType(),
            );
    }

    public function relationships()
    {
        return array(
            'Powers' => Power::hasMany('id','heroid'),
            'SecretIdentity' => SecretIdentity::belongsTo('identityid','id', true),
            );
    }

    public static function createHelper($alias, $identity, $powers=array())
    {
        $hero = new Hero(array('alias'=>$alias));
        $hero->save();

        $identity = new SecretIdentity(array('realname'=>$identity));
        $hero->SecretIdentity = $identity;
        $identity->save();

        foreach ($powers as $power) {
            $power = new Power(array('description'=>$power));
            $hero->Powers []= $power;
            $power->save();
        }

        $hero->save();

        return $hero;
    }

}
