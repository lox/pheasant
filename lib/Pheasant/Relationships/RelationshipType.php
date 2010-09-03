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

	public function get($object, $key)
	{
		throw new \BadMethodCallException(
			"Get not supported on ".get_class($this));
	}

	public function set($object, $key, $value)
	{
		throw new \BadMethodCallException(
			"Set not supported on ".get_class($this));
	}

	public function add($object, $value)
	{
		throw new \BadMethodCallException(
			"Add not supported on ".get_class($this));
	}

	/**
	 * Delegates to the finder for querying
	 * @return Query
	 */
	protected function query($sql, $params)
	{
		$finder = \Pheasant::instance()->finderFor($this->class);
		return $finder->query($sql, $params);
	}

	/**
	 * Delegates to the schema for hydrating
	 * @return DomainObject
	 */
	protected function hydrate($row, $saved=true)
	{
		return \Pheasant::instance()->schema($this->class)->hydrate($row, $saved);
	}

	/**
	 * Helper function that creates a closure that calls the add function
	 * @return Closure
	 */
	protected function adder($object)
	{
		$rel = $this;
		return function($value) use($object, $rel) {
			return $rel->add($object, $value);
		};
	}
}
