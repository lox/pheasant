<?php

namespace pheasant;

class Properties
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

	public function serial($name, $options=array())
	{
		$array = array_merge($this->defaults(), array(
			'name'=>$name,
			'type'=>'sequence',
			));

		return $this->addProperty($name, new Property($options, $array));
	}

	public function string($name, $length=255, $options=array())
	{
		$array = array_merge($this->defaults(), array(
			'name'=>$name,
			'type'=>'string',
			'length'=>$length,
			));

		return $this->addProperty($name, new Property($options, $array));
	}

	public function integer($name, $length=4, $options=array())
	{
		$array = array_merge($this->defaults(), array(
			'name'=>$name,
			'type'=>'integer',
			'length'=>$length,
			));

		return $this->addProperty($name, new Property($options, $array));
	}

	public function defaults()
	{
		return array(
			'primary'=>false,
			'required'=>false,
			'auto_increment'=>false,
			'default'=>null,
			);
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
}
