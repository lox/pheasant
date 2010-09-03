<?php

namespace Pheasant\Relationships;

use \Pheasant\Collection;

/**
 * A HasMany relationship represents a 1 to N relationship.
 */
class HasMany extends RelationshipType
{
	/**
	 * Constructor
	 */
	public function __construct($class, $local, $foreign=null)
	{
		parent::__construct('hasmany', $class, $local, $foreign);
	}

	/* (non-phpdoc)
	 * @see RelationshipType::get()
	 */
	public function get($object, $key)
	{
		$query = $this->query(
			"{$this->foreign}=?", $object->get($this->local));

		return new Collection(get_class($object), $query, $this->adder($object));
	}

	/* (non-phpdoc)
	 * @see RelationshipType::add()
	 */
	public function add($object, $value)
	{
		$value->set($this->foreign, $object->get($this->local));
	}
}
