<?php

namespace Pheasant\Types;

/**
 * A big integer type
 */
class BigIntegerType extends IntegerType
{
    /* (non-phpdoc)
     * @see \Pheasant\Type::columnSql
     */
    public function columnSql($column, $platform)
    {
        $type = $this->width ? "bigint({$this->width})" : "bigint";

        return $platform->columnSql($column, $type, $this->options());
    }
}
