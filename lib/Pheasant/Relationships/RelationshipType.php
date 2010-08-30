<?php

namespace Pheasant\Relationships;

class RelationshipType
{
	public $type, $class, $local, $foreign;

	public function __construct($type, $class, $local, $foreign=null)
	{
		$this->type = $type;
		$this->class = $class;
		$this->local = $local;
		$this->foreign = empty($foreign) ? $local : $foreign;
	}

	public function callGet($object, $key)
	{
		throw new \BadMethodCallException(
			"Get not supported on ".get_class($this));
	}

	public function callSet($object, $key, $value)
	{
		throw new \BadMethodCallException(
			"Set not supported on ".get_class($this));
	}
}
