<?php

namespace Pheasant\Tests\Examples;

use \Pheasant\DomainObject;
use \Pheasant\Types;
use \Pheasant\Mapper\RowMapper;
use \Pheasant\Types\SequenceType;
use \Pheasant\Types\StringType;

class Person extends DomainObject
{
    public static function initialize($builder, $pheasant)
    {
        $pheasant
            ->register(__CLASS__, new RowMapper('person'));

        $builder
            ->properties(array(
                'personid' => new SequenceType('personid'),
                'name' => new StringType(),
            ));
    }
}
