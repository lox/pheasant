<?php

namespace pheasant;

class Properties implements \IteratorAggregate
{
	private $_properties;
	private $_primary=array();

	public function addProperty($name, $property)
	{
		$this->_properties[$name] = $property;

		// track primary properties
		if($property->primary)
			$this->_primary[] = $name;

		return $this;
	}

	public function sequence($name, $options=array())
	{
		return $this->addProperty($name, new Property($this->_options($options, array(
			'name'=>$name,
			'type'=>'integer',
			'length'=>8,
			'primary'=>true,
			'sequence'=>true,
			))));
	}

	public function string($name, $length=255, $options=array())
	{
		return $this->addProperty($name, new Property($this->_options($options, array(
			'name'=>$name,
			'type'=>'string',
			'length'=>$length,
			))));
	}

	public function integer($name, $length=4, $options=array())
	{
		return $this->addProperty($name, new Property($this->_options($options, array(
			'name'=>$name,
			'type'=>'integer',
			'length'=>$length,
			))));
	}

	private function _options($options, $defaults)
	{
		$object = new Options(array(
			'primary'=>false,
			'required'=>false,
			'auto_increment'=>false,
			'default'=>null,
			'sequence'=>false,
			));

		return $object
			->merge($defaults)
			->merge($options)
			;
	}

	public function primaryKeys()
	{
		$properties = array();

		foreach($this->_primary as $key)
			$properties[$key] = $this->{$key};

		return $properties;
	}

	public function __isset($key)
	{
		return isset($this->_properties[$key]);
	}

	public function __get($key)
	{
		return $this->_properties[$key];
	}

	public function getIterator()
	{
		return new \ArrayIterator($this->_properties);
	}
}
