<?php

namespace Pheasant;

/**
 * A property represents a scalar value associated with a domain object
 */
class Property
{
    public $name, $type;

    /**
     * Constructor
     */
    public function __construct($name, $type)
    {
        $this->name = $name;
        $this->type = $type;
    }

    /**
     * Returns the name of the property
     */
    public function __toString()
    {
        return $this->name;
    }

    /**
     * Returns a bool for whether the property has a default value
     * @return bool
     */
    public function hasDefaultValue()
    {
        return isset($this->type->options()->default);
    }

    /**
     * Returns the default value for a property, or NULL
     */
    public function defaultValue()
    {
        return $this->hasDefaultValue()
            ? $this->type->options()->default
            : NULL
            ;
    }

    /**
     * Return a closure for accessing the value of the property
     * @return closure
     */
    public function getter($key)
    {
        $property = $this;

        return function($object) use ($key, $property) {
            $value = $object->get($key);

            if (is_null($value) && $property->type->options()->primary) {
                return $property->reference($object);
            } else {
                return $value;
            }
        };
    }

    /**
     * Return a closure that when called sets the value of the property
     * @return closure
     */
    public function setter($key)
    {
        return function($object, $value) use ($key) {
            return $object->set($key, $value);
        };
    }

    /**
     * Returns a reference to the property value of a specific object
     * @return PropertyReference
     */
    public function reference($object)
    {
        return new PropertyReference($this, $object);
    }
}
