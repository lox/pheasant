<?php

namespace Pheasant\Tests\Examples;

use \Pheasant\DomainObject;
use \Pheasant\Types;
use \Pheasant\Mapper\RowMapper;
use \Pheasant\Types\Sequence;
use \Pheasant\Types\String;

class Hero extends DomainObject
{
	public function properties()
	{
		return array(
			'heroid' => new Types\Sequence(),
			'alias' => new Types\String(),
			'identityid' => new Types\Integer(),
			);
	}

	public function relationships()
	{
		return array(
			'Powers' => Power::hasMany('heroid'),
			'SecretIdentity' => SecretIdentity::belongsTo('identityid'),
			);
	}
}


