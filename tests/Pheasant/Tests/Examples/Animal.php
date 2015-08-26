<?php

namespace Pheasant\Tests\Examples;

use \Pheasant\DomainObject;
use \Pheasant\Types;
use \Pheasant\Mapper\RowMapper;

class Animal extends DomainObject
{
    public static function initialize($builder, $pheasant)
    {
        $pheasant
            ->register(__CLASS__, new RowMapper('animal'));

        $builder
            ->properties(array(
                'id' => new Types\IntegerType(11, 'primary auto_increment'),
                'type' => new Types\StringType(255, 'required default=llama'),
                'name' => new Types\StringType(255),
            ));
    }

    public static function scopes()
    {
        return array(
            'frogs' => function($chain){ return $chain->filter('type = ?', 'frog'); },
            'by_type' => function($chain, $type){ return $chain->filter('type = ?', $type); },
        );
    }
}
