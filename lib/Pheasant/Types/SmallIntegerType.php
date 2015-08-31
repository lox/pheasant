<?php

namespace Pheasant\Types;

/**
 * A small integer type
 */
class SmallIntegerType extends IntegerType
{
    /* (non-phpdoc)
     * @see \Pheasant\Type::columnSql
     */
    public function columnSql($column, $platform)
    {
        $type = $this->width ? "smallint({$this->width})" : "smallint";

        return $platform->columnSql($column, $type, $this->options());
    }
}
