<?php

namespace Pheasant;

class MockLoader
{
	private $_mocks=array();

	public function load($className)
	{
		if(!isset($this->_mocks[$className]))
			return false;

		$hierarchy = explode('\\', $className);
		$className = array_pop($hierarchy);
		$namespace = implode('\\', $hierarchy);

		// unfortunately class_alias doesn't work
		eval(sprintf(
			"namespace %s;\n\nclass %s extends \Pheasant\MockProxy {}",
			$namespace, 
			$className
		));

		return true;
	}

	public function mock($className, $callback)
	{
		$this->_mocks[$className] = $callback;
		return $this;
	}

	public function mockFor($className)
	{
		return call_user_func($this->_mocks[get_class($className)]);
	}

	public function register()
	{
		spl_autoload_register(array($this,'load'), true, true);
		return $this;
	}
}
