<?php

namespace Pheasant\Types;

use \Pheasant\Options;

/**
 * An abstract type base class
 */
abstract class BaseType implements \Pheasant\Type
{
    private $_options;

    /**
     * Constructor
     */
    public function __construct($options=null)
    {
        $this->_options = Options::coerce($options);
    }

    /**
     * Gets the {@link Options} object associated with the type
     */
    public function options()
    {
        return $this->_options;
    }

    /* (non-phpdoc)
     * @see \Pheasant\Type::unmarshal
     */
    public function unmarshal($value)
    {
        return $value;
    }

    /* (non-phpdoc)
     * @see \Pheasant\Type::marshal
     */
    public function marshal($value)
    {
        return $value;
    }

    /* (non-phpdoc)
     * @see \Pheasant\Type::equals
     */
    public function equals($value1, $value2)
    {
        return $value1 == $value2;
    }
}
