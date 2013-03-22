<?php

namespace Pheasant\Types;

/**
 * A basic string type
 */
class Boolean extends Base
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
}
