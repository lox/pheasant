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
}
