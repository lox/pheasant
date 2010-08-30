<?php

namespace Pheasant\Relationships;

use \Pheasant\Collection;

class HasMany extends RelationshipType
{
	public function __construct($class, $local, $foreign=null)
	{
		parent::__construct('hasmany', $class, $local, $foreign);
	}

	public function closureGet($object)
	{
		$rel = $this;
		$finder = \Pheasant::instance()->finderFor($this->class);

		return function($key) use($object, $finder, $rel) {
			$query = $finder->query("{$rel->foreign}=?", $object->get($rel->local));
			return new Collection(get_class($object), $query, $rel->closureAdd($object));
		};
	}

	public function closureAdd($object)
	{
		$rel = $this;

		return function($value) use($object, $rel) {
			$value->set($rel->foreign, $object->get($rel->local));
		};
	}
}
