<?php

namespace Pheasant;

class Identity implements \IteratorAggregate
{
    private $_properties, $_object;

    public function __construct($properties, $object)
    {
        $this->_properties = $properties;
        $this->_object = $object;
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->_properties);
    }

    public function toArray()
    {
        $array = array();

        foreach($this->_properties as $property)
            $array[$property->name] = $this->_object->get($property->name);

        return $array;
    }

    public function toCriteria()
    {
        return new Query\Criteria($this->toArray());
    }

    public function __toString()
    {
        $array = $this->toArray();

        $keyValues = array_map(
            function ($k) use ($array) {
                return sprintf('%s=%s', $k, $array[$k]);
            },
            array_keys($array)
        );

        return sprintf('[%s]', implode(',', $keyValues));
    }
}
