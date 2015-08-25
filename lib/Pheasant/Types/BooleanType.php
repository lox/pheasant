<?php

namespace Pheasant\Types;

use \Pheasant\Database\TypedValue;

/**
 * A basic string type
 */
class BooleanType extends BaseType
{
    /* (non-phpdoc)
     * @see \Pheasant\Type::columnSql
     */
    public function columnSql($column, $platform)
    {
        return $platform->columnSql($column, "boolean", $this->options());
    }

    /* (non-phpdoc)
     * @see \Pheasant\Type::unmarshal
     */
    public function unmarshal($value)
    {
        return (bool) $value;
    }

    /* (non-phpdoc)
     * @see \Pheasant\Type::marshal
     */
    public function marshal($value)
    {
        return new TypedValue((bool)$value);
    }
}
