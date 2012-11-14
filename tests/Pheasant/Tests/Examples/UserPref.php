<?php

namespace Pheasant\Tests\Examples;

use \Pheasant\DomainObject;
use \Pheasant\Types;
use \Pheasant\Mapper\RowMapper;
use \Pheasant\Types\Sequence;
use \Pheasant\Types\String;

class UserPref extends DomainObject
{
	public function properties()
	{
		return array(
			'userid' => new Types\Integer(),
			'pref' => new Types\String(),
			'value' => new Types\String(),
			);
	}

	public function relationships()
	{
		return array(
			'User' => User::belongsTo('userid')
			);
	}
}
