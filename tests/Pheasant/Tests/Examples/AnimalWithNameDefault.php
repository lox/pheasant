<?php

namespace Pheasant\Tests\Examples;

use \Pheasant\DomainObject;
use \Pheasant\Types;
use \Pheasant\Mapper\RowMapper;

class AnimalWithNameDefault extends DomainObject
{
    public static function initialize($builder, $pheasant)
    {
        $pheasant
            ->register(__CLASS__, new RowMapper('animal'));

        $builder
            ->properties(array(
                'id' => new Types\IntegerType(11, 'primary auto_increment'),
                'type' => new Types\StringType(255, 'required default=llama'),
                'name' => new Types\StringType(255, 'default=blargh'),
            ));
    }
}
