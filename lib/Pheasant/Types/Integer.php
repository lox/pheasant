<?php

namespace Pheasant\Types;

/**
 * A basic integer type
 */
class Integer extends Type
{
    const TYPE='integer';

    /**
     * Constructor
     */
    public function __construct($length=11, $options=null)
    {
        parent::__construct(self::TYPE, $length, $options);
    }
}
