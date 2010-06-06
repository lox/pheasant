<?php

namespace pheasant;

/**
 * An array-like structure that supports defaults and and numerically indexed keys.
 * Expands any options from int=>key to key=>$default
 */
class Options implements \IteratorAggregate
{
	private $_options=array();

	/**
	 *
	 */
	public function __construct($options, $default=true)
	{
		$this->merge($options, $default);
	}

	/**
	 * Merges a new array into the options structure
	 */
	public function merge($array, $default=true)
	{
		$newarray = array();

		foreach($array as $key=>$value)
		{
			$newKey = is_numeric($key) ? $value : $key;
			$newValue = is_numeric($key) ? true : $value;
			$newarray[$newKey] = $newValue;
		}

		$this->_options = array_merge($this->_options, $newarray);
		return $this;
	}

	public function getIterator()
	{
		return new \ArrayIterator($this->_options);
	}

	public function __isset($key)
	{
		return isset($this->_options[$key]);
	}

	public function __get($key)
	{
		return $this->_options[$key];
	}
}
