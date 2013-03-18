<?php

namespace Pheasant\Types;

use \Pheasant\Options;

/**
 * A column type in domain object, corresponds to a database type
 */
class Type
{
    public $typename, $length, $options;

    /**
     * @param $type the name of the type
     * @param $length the length of the type
     * @param $options an {@link Options} object
     */
    public function __construct($type, $length=null, $options=null)
    {
        $this->type = $type;
        $this->length = $length;
        $this->options = is_object($options) ? $options : Options::fromString($options);
    }
}
