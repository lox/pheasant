<?php

namespace pheasant;

class Property
{
	public function __construct($options, $defaults=array())
	{
		foreach(array_merge($defaults, $this->_expand($options)) as $key=>$value)
		{
			$this->{$key} = $value;
		}
	}

	/**
	 * Converts an array like array('a','b'=>5) into array('a'=>true, 'b'=>5)
	 */
	private static function _expand($array, $default=true)
	{
		$result = array();
		foreach($array as $key=>$value)
		{
			if(is_numeric($key))
			{
				$key = $value;
				$value = $default;
			}

			$result[$key] = $value;
		}

		return $result;
	}
}
