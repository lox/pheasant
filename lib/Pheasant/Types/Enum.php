<?php

namespace Pheasant\Types;

/**
 * ENUM data integrity without the shortfalls of MySQL ENUM
 */
class Enum extends String
{
    private $_allowedValues;

    /**
     * Constructor
     */
    public function __construct(Array $allowedValues, $options = null)
    {
        parent::__construct(max(array_map('strlen', $allowedValues)), $options);
        $this->_allowedValues = $allowedValues;
    }

    public function marshal($value)
    {
        if (!in_array($value, $this->_allowedValues)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Allowed values for this field are %s.',
                    implode(', ', $this->_allowedValues)
                )
            );
        }

        return $value;
    }
}
