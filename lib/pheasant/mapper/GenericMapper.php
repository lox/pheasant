<?php

namespace pheasant\mapper;
use pheasant\Pheasant;

/**
 * A generic mapper object that provides infrastructure for other mappers
 */
class GenericMapper implements Mapper
{
	public function save($object)
	{
		if(!$object->isSaved())
		{
			$this->insert($object);
		}
		else if($changes = $object->changes())
		{
			$this->update($object, $changes);
		}

		return $this;
	}

	protected function insert($object)
	{
		throw new \BadMethodCallException("Insert not implemented");
	}

	protected function update($object, $changes)
	{
		throw new \BadMethodCallException("Update not implemented");
	}

	public function delete($object)
	{
		throw new \BadMethodCallException("Delete not implemented");
	}
}
