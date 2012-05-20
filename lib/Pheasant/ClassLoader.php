<?php

namespace Pheasant;

class ClassLoader
{
	public function classFile($className)
	{
		return __DIR__ . '/../' . str_replace('\\','/',$className).'.php';
	}

	public function load($className)
	{
		if(!class_exists($className))
		{
			$path = $this->classFile($className);

			if(file_exists($path))
			{
				require_once($path);
				return true;
			}

			if(!class_exists($className) && !interface_exists($className))
				return false;
		}
	}

	public function register()
	{
		spl_autoload_register(array($this,'load'));
		return $this;
	}
}
