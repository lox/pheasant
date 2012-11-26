<?php

namespace Pheasant;
use \Pheasant;

class InstanceCache
{
	private $_cache = array();

	public function has($key)
	{
		return isset($this->_cache[$key]);
	}

	public function set($key, $value)
	{
		$this->_cache[$key] = $value;
	}

	public function get($key)
	{
		return $this->_cache[$key];
	}

	public function del($key)
	{
		unset($this->_cache[$key]);
	}
}