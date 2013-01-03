<?php

namespace Pheasant\Types;

/**
* A fixed width character type
 */
class Character extends Type
{
    const TYPE='character';

    /**
     * Constructor
     */
    public function __construct($length, $params=null)
    {
        parent::__construct(self::TYPE, $length, $params);
    }
}
