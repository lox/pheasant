<?php

namespace Pheasant;

class Registry
{
	private $_registry=array();
	private $_default=false;

	public function register($key, $mixed)
	{
		$this->_registry[$key] = $mixed;
	}

	public function lookup($key)
	{
		if(!isset($this->_registry[$key]))
		{
			if($this->_default)
			{
				$this->_registry[$key] = call_user_func($this->_default, $key);
				return $this->_registry[$key];
			}
			else
			{
				throw new \Exception("Nothing registered for $key");
			}
		}
		else if(is_callable($this->_registry[$key]))
		{
			$this->_registry[$key] = call_user_func($this->_registry[$key], $key);
			return $this->_registry[$key];
		}
		else
		{
			return $this->_registry[$key];
		}
	}

	public function setDefaultCallback($callback)
	{
		$this->_default = $callback;
	}
}

