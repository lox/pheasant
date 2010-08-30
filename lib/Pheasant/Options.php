<?php

namespace Pheasant;

/**
 * An array-like structure that supports defaults and and numerically indexed keys.
 * Expands any options from int=>key to key=>$default
 */
class Options implements \IteratorAggregate
{
	private $_options=array();

	/**
	 * Constructor
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
		return isset($this->_options[$key]) ? $this->_options[$key] : false;
	}

	public static function fromString($string, $default=true)
	{
		$array = array();

		foreach(explode(" ", $string) as $token)
		{
			$fragments = explode("=", $token);
			$array[$fragments[0]] = isset($fragments[1]) ? $fragments[1] : $default;
		}

		return new self($array, $default);
	}
}
