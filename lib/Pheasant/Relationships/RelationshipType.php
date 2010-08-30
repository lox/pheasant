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

	public function closureGet($object)
	{
		return function($key) use($object) {
			throw new \BadMethodCallException('Get not supported');
		};
	}

	public function closureSet($object)
	{
		return function($key, $value) use($object) {
			throw new \BadMethodCallException('Set not supported');
		};
	}

	public function closureAdd($object)
	{
		return function($value) use($object) {
			throw new \BadMethodCallException('Add not supported');
		};
	}

	public function closureRemove($object)
	{
		return function($key) use($object) {
			throw new \BadMethodCallException('Remove not supported');
		};
	}
}
