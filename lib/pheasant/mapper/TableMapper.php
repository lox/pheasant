<?php

namespace pheasant\mapper;
use pheasant\Pheasant;

class TableMapper implements Mapper
{
	public function save($object)
	{
		$schema = Pheasant::schema($object);
		$table = Pheasant::connection()->table($schema->table());
		$data = array();

		// build a changeset
		foreach($object->changes() as $change)
			$data[$change] = $object->get($change, false, null);

		if(!$object->isSaved())
		{
			$result = $table->insert($data);

			// check for auto-increment
			foreach($schema->properties()->primaryKeys() as $key=>$property)
			{
				if($property->auto_increment)
					$object->{$key} = $result->lastInsertId();
			}
		}
		else if($data)
		{
			$keys = array();

			// check for auto-increment
			foreach($schema->properties()->primaryKeys() as $key=>$property)
			{
				$keys[$key] = $object->{$key};
			}

			$result = $table->update($data, $keys);
		}

		$object->checkpoint();
		return $this;
	}

	public function delete($object)
	{
	}
}
