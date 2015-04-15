<?php

namespace Pheasant\Cache;

use \Pheasant\Identity;

/**
 * An in-memory array backed cache
 */
class ArrayCache implements \Pheasant\Cache
{
    private $_cache=array();

    public function has($hash)
    {
        return isset($this->_cache[(string) $hash]);
    }

    public function get($hash)
    {
        // hack to avoid an extra isset check
        $value = @$this->_cache[(string) $hash];

        return $value ? $value : false;
    }

    public function add($object)
    {
        $this->_cache[(string) $object->identity()] = $object;
    }

    public function clear()
    {
        $this->_cache = array();
    }
}
