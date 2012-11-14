<?php

namespace Pheasant\Tests\Examples;

use \Pheasant\DomainObject;
use \Pheasant\Types;
use \Pheasant\Mapper\RowMapper;
use \Pheasant\Types\Sequence;
use \Pheasant\Types\String;

class SecretIdentity extends DomainObject
{
	public function properties()
	{
		return array(
			'identityid' => new Types\Sequence(),
			'realname' => new Types\String(),
			);
	}

	public function relationships()
	{
		return array(
			'Hero' => Hero::hasOne('identityid')
			);
	}
}
