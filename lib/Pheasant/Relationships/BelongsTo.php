<?php

namespace Pheasant\Relationships;

class BelongsTo extends RelationshipType
{
	public function __construct($class, $local, $foreign=null)
	{
		parent::__construct('belongsto', $class, $local, $foreign);
	}
}
