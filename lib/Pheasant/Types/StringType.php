<?php

namespace Pheasant\Types;

use \Pheasant\Database\TypedValue;

/**
 * A basic string type
 */
class StringType extends BaseType
{
    private $_length;

    /**
     * Constructor
     */
    public function __construct($length = 255, $options = null)
    {
        parent::__construct($options);
        $this->_length = intval($length);
    }

    /* (non-phpdoc)
     * @see \Pheasant\Type::columnSql
     */
    public function columnSql($column, $platform)
    {
        if ($this->_length <= 255) {
            return $platform->columnSql($column, "varchar({$this->_length})", $this->options());
        } elseif ($this->_length <= 65534) {
            return $platform->columnSql($column, "text", $this->options());
        } elseif ($this->_length <= 16777214) {
            return $platform->columnSql($column, "mediumtext", $this->options());
        } elseif ($this->_length <= 4294967294) {
            return $platform->columnSql($column, "longtext", $this->options());
        } else {
            throw new \BadMethodCallException("Unhandled string length of {$this->_length}");
        }
    }

    /* (non-phpdoc)
     * @see \Pheasant\Type::marshal
     */
    public function marshal($value)
    {
        if ($this->options()->allowed && !in_array($value, $this->options()->allowed)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Allowed values for this field are %s.',
                    implode(', ', $this->options()->allowed)
                )
            );
        }
        return new TypedValue((string) $value);
    }
}
