<?php

namespace Pheasant\Types;

use \Pheasant\Database\TypedValue;

/**
 * A basic integer type
 */
class IntegerType extends BaseType
{
    public $width;

    /**
     * Constructor
     */
    public function __construct($width=null, $options=null)
    {
        parent::__construct($options);
        $this->width = intval($width);
    }

    /* (non-phpdoc)
     * @see \Pheasant\Type::columnSql
     */
    public function columnSql($column, $platform)
    {
        $type = $this->width ? "int({$this->width})" : "int";

        return $platform->columnSql($column, $type, $this->options());
    }

    /* (non-phpdoc)
     * @see \Pheasant\Type::unmarshal
     */
    public function unmarshal($value)
    {
        return (int) $value;
    }

    /* (non-phpdoc)
     * @see \Pheasant\Type::marshal
     */
    public function marshal($value)
    {
        return new TypedValue((int) $value);
    }
}
