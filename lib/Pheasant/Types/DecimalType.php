<?php

namespace Pheasant\Types;

use \Pheasant\Database\TypedValue;

/**
 * A basic decimal type
 */
class DecimalType extends BaseType
{
    private $_length, $_scale;

    /**
     * Constructor
     */
    public function __construct($length=10, $scale=2, $options=null)
    {
        parent::__construct($options);
        $this->_length = intval($length);
        $this->_scale = intval($scale);
    }

    /* (non-phpdoc)
     * @see \Pheasant\Type::columnSql
     */
    public function columnSql($column, $platform)
    {
        return $platform->columnSql($column, "decimal({$this->_length},{$this->_scale})", $this->options());
    }

    /* (non-phpdoc)
     * @see \Pheasant\Type::unmarshal
     */
    public function unmarshal($value)
    {
        return (float)$value;
    }

    /* (non-phpdoc)
     * @see \Pheasant\Type::marshal
     */
    public function marshal($value)
    {
        return new TypedValue((float) $value);
    }
}
