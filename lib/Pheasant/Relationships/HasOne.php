<?php

namespace Pheasant\Relationships;

/**
 * A BelongsTo relationship represents a 1->1 relationship
 */
class HasOne extends RelationshipType
{
	/**
	 * Constructor
	 */
	public function __construct($class, $local, $foreign=null)
	{
		parent::__construct('hasone', $class, $local, $foreign);
	}

	/* (non-phpdoc)
	 * @see RelationshipType::get()
	 */
	public function get($object, $key)
	{
		$query = $this->query(
			"{$this->foreign}=?", $object->get($this->local));

		return $this->hydrate($query->execute()->fetch(), true);
	}

	/* (non-phpdoc)
	 * @see RelationshipType::set()
	 */
	public function set($object, $key, $value)
	{
		var_dump("setting ".get_class($object),$object,$key,$value);
		var_dump($this->local, $this->foreign);

		$object->set($this->local, $value->get($this->foreign));
	}
}
