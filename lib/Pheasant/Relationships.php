<?php

namespace Pheasant;

class Relationships
{
	private $_schema;
	private $_relationships;

	public function __construct($schema)
	{
		$this->_schema = $schema;
		$this->_relationships = new \stdClass();
	}

	public function hasOne($name, $mapper, $criteria)
	{
		$this->_relationships->$name = (object) array(
			'schema'=>$this->_schema,
			'type'=>'hasone',
			'name'=>$name,
			'mapper'=>$mapper,
			'criteria'=>$criteria,
			);
	}

	public function hasMany($name, $mapper, $criteria)
	{
		$this->_relationships->$name = (object) array(
			'schema'=>$this->_schema,
			'type'=>'hasmany',
			'name'=>$name,
			'mapper'=>$mapper,
			'criteria'=>$criteria,
			);
	}

	public function belongsTo($name, $mapper, $criteria)
	{
		$this->_relationships->$name = (object) array(
			'schema'=>$this->_schema,
			'type'=>'belongsto',
			'name'=>$name,
			'mapper'=>$mapper,
			'criteria'=>$criteria
			);
	}

	public function __get($key)
	{
		if(!isset($this->_relationships->$key))
			throw new Exception("Unknown relationship $key");

		return $this->_relationships->$key;
	}
}
