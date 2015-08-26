<?php

namespace Pheasant\Tests\Examples;

use \Pheasant\DomainObject;
use \Pheasant\Types;
use \Pheasant\Mapper\RowMapper;

class Post extends DomainObject
{
    public static function initialize($builder, $pheasant)
    {
        $pheasant
            ->register(__CLASS__, new RowMapper('post'));

        $builder
            ->properties(array(
                'postid' => new Types\IntegerType(11, 'primary auto_increment'),
                'title' => new Types\StringType(255, 'required'),
                'subtitle' => new Types\StringType(255),
            ));
    }

    protected function construct($title)
    {
        $this->title = $title;
    }
}
