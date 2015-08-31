<?php

namespace Pheasant\Types;

/**
 * A Set type
 */
class SetType extends BaseType
{
    private $_set;

    /**
     * Constructor
     */
    public function __construct($set = array(), $options=null)
    {
        parent::__construct($options);
        $this->_set = $set;
    }

    /* (non-phpdoc)
     * @see \Pheasant\Type::columnSql
     */
    public function columnSql($column, $platform)
    {
        return $platform->columnSql($column, "set('" . implode("','", $this->_set) . "')", $this->options());
    }

    /* (non-phpdoc)
     * @see \Pheasant\Type::unmarshal
     */
    public function unmarshal($value)
    {
        return explode(',',$value);
    }

    /* (non-phpdoc)
     * @see \Pheasant\Type::marshal
     */
    public function marshal($value)
    {
        return implode(',',$value);
    }
}
