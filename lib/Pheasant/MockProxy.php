<?php

namespace Pheasant;

class MockProxy
{
	private $_delegate;

	private function _mockDelegate()
	{
		if(!isset($this->_delegate))
			$this->_delegate = \Pheasant::instance()->mockLoader()->mockFor($this);

		return $this->_delegate;
	}

	public function __call($method, $params)
	{
		return call_user_func_array(array($this->_mockDelegate(), $method), $params);
	}

	public function __get($prop)
	{
		return $this->_mockDelegate()->$prop;
	}

	public function __set($prop, $value)
	{
		$this->_mockDelegate()->$prop = $value;
	}
}

