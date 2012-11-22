<?php

namespace Pheasant;

class Relationship
{
  public $name, $type;

  public function __construct($name, $type)
  {
    $this->name = $name;
    $this->type = $type;
  }

  public function __toString()
  {
    return $this->name;
  }

  // -------------------------------------
  // delegate double dispatch calls to type

  public function getter($key)
  {
    $type = $this->type;
    return function($object) use($key, $type) {
      return $type->get($object, $key);
    };
  }

  public function setter($key)
  {
    $type = $this->type;
    return function($object, $value) use($key, $type) {
      return $type->set($object, $key, $value);
    };
  }
}
