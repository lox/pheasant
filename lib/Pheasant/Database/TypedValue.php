<?php

namespace Pheasant\Database;

/**
 * A wrapped for a value where the php type can be used
 * to map to the underlying database type
 */
class TypedValue
{
    public $value;

    public function __construct($value)
    {
        $this->value = $value;
    }

    public function toSql($binder)
    {
        if(is_int($this->value) || is_float($this->value)) {
            return $this->value;
        }

        // default to quoted strings
        return $binder->quote($binder->escape($this->value));
    }
}