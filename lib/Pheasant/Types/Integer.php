<?php

namespace Pheasant\Types;

/**
 * A basic integer type
 */
class Integer extends Base
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
}
