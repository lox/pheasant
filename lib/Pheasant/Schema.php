<?php

namespace Pheasant;

class Schema
{
	private $_properties;
	private $_relationships;
	private $_table;

	public function properties()
	{
		if(!isset($this->_properties))
			$this->_properties = new Properties();

		return $this->_properties;
	}

	public function relationships()
	{
		if(!isset($this->_relationships))
			$this->_relationships = new Relationships($this);

		return $this->_relationships;
	}

	/**
	 * Setter/Getter for table name
	 */
	public function table($name=null)
	{
		if($name) $this->_table = $name;
		return $this->_table;
	}

	public function identity($object)
	{
		return new Identity($object);
	}
}
