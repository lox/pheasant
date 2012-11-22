<?php

namespace Pheasant\Tests\Examples;

use \Pheasant\DomainObject;
use \Pheasant\Types;
use \Pheasant\Mapper\RowMapper;

class MyDomainObject extends DomainObject
{
  public function afterSave()
  {
    $this->test = 'blargh';
  }
}


