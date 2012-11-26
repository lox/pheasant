<?php

namespace Pheasant\Relationships;

/**
 * A BelongsTo relationship represents the weak side of a 1->1 relationship. The
 * local entity has responsibility for the foreign key.
 *
 */
class BelongsTo extends HasOne
{
	/* (non-phpdoc)
	 * @see RelationshipType::get()
	 */
	public function get($object, $key)
	{
		$query = $this->query(
			"{$this->foreign}=?", $object->get($this->local));

		$cache = $object->instanceCache();
		if (!$cache->has($key))
			$cache->set($key, $this->hydrate($query->execute()->row(), true));
		return $cache->get($key);
	}

	/* (non-phpdoc)
	 * @see RelationshipType::set()
	 */
	public function set($object, $key, $value)
	{
		$object->instanceCache()->set($key, $value);
		$object->set($this->local, $value->{$this->foreign});
	}
}
