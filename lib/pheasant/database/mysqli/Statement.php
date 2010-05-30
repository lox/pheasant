<?php

namespace pheasant\database\mysqli;

class Statement
{
	private $_link;
	private $_statement;
	private $_sql;

	public function __construct($link, $sql)
	{
		$this->_link = $link;
		$this->_sql = $sql;
		$this->_statement = $link->prepare($sql);
	}

	public function execute($params=array())
	{
		foreach($params as $param)
			$this->_statement->bind_param('s', $param);

		$this->_statement->execute();
		var_dump($this->_statement->result_metadata());
	}
}
