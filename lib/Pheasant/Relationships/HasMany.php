<?php

namespace Pheasant\Relationships;

class HasMany extends RelationshipType
{
	public function __construct($class, $local, $foreign=null)
	{
		parent::__construct('hasmany', $class, $local, $foreign);
	}

	public function callGet($object, $key)
	{
		$finder = \Pheasant::instance()->finderFor($this->class);
		return $finder->find($this->class, "{$this->foreign}=?", $object->get($this->local));
	}

	public function callSet($object, $key, $value)
	{
		var_dump(array(__METHOD__, func_get_args(), $this));
		//return $object->set($key, $value);
	}
}
