<?php

namespace Pheasant\Types;

/**
 * A basic character type
 */
class CharacterType extends BaseType
{
    private $_length;

    /**
     * Constructor
     */
    public function __construct($length, $options=null)
    {
        parent::__construct($options);
        $this->_length = intval($length);
    }

    /* (non-phpdoc)
     * @see \Pheasant\Type::columnSql
     */
    public function columnSql($column, $platform)
    {
        return $platform->columnSql($column, "char({$this->_length})", $this->options());
    }
}
