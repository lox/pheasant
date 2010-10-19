<?php

namespace Pheasant\Relationships;

/**
 * A HasOne relationship represents a 1->1 relationship. The foreign domain object
 * is responsible for maintaining a key referencing a local attribute.
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
		$result = $this
			->query("{$this->foreign}=?", $object->{$this->local})
			->execute();
			;

		// TODO: is this the correct behaviour?
		if(!count($result))
			throw new \Pheasant\Exception("Failed to find a $key (via $this->foreign)");

		return $this->hydrate($query->execute()->fetch(), true);
	}

	/* (non-phpdoc)
	 * @see RelationshipType::set()
	 */
	public function set($object, $key, $value)
	{
		$value->set($this->local, $object->{$this->foreign});
	}
}
