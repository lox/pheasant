<?php

namespace Pheasant\Types;

use \Pheasant\Database\TypedValue;

/**
 * A date and time type that persists to a unix timestamp
 */
class UnixTimestampType extends BaseType
{
    /* (non-phpdoc)
     * @see \Pheasant\Type::columnSql
     */
    public function columnSql($column, $platform)
    {
        return $platform->columnSql($column, 'int', $this->options());
    }

    /* (non-phpdoc)
     * @see \Pheasant\Type::unmarshal
     */
    public function unmarshal($value)
    {
        return new \DateTime('@'.$value);
    }

    /* (non-phpdoc)
     * @see \Pheasant\Type::marshal
     */
    public function marshal($value)
    {
        return new TypedValue($value->getTimestamp());
    }
}
