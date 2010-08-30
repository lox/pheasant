<?php

namespace Pheasant\Relationships;

use \Pheasant\Collection;

class HasOne extends RelationshipType
{
	public function __construct($class, $local, $foreign=null)
	{
		parent::__construct('hasone', $class, $local, $foreign);
	}

	public function closureGet($object)
	{
		$rel = $this;
		$class = $this->class;
		$finder = \Pheasant::instance()->finderFor($class);

		// TODO: rewrite this code when sober
		return function($key) use($object, $finder, $rel, $class) {
			$query = $finder->query("{$rel->foreign}=?", $object->get($rel->local));
			return $class::fromArray($query->execute()->fetch(), true);
		};
	}

	public function closureSet($object)
	{
		$rel = $this;

		return function($key, $value) use($object, $rel) {
			$object->set($rel->local, $value->get($rel->foreign));
		};
	}
}
