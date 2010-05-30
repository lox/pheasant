<?php

namespace pheasant;

class Identity
{
	private $_object;

	public function __construct(DomainObject $object)
	{
		$this->_object = $object;
	}

	public function hasProperty($property)
	{
		return in_array($property,
			$this->_object->schema()->properties()->primaryKeys());
	}
}
