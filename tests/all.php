<?php

require_once('autorun.php');

class AllTests extends TestSuite
{
	function __construct()
	{
		parent::__construct('All tests');
		$this->addFile(dirname(__FILE__).'/db.php');
		$this->addFile(dirname(__FILE__).'/sequences.php');
		$this->addFile(dirname(__FILE__).'/transaction.php');
		$this->addFile(dirname(__FILE__).'/mapping.php');
		$this->addFile(dirname(__FILE__).'/finding.php');
		$this->addFile(dirname(__FILE__).'/query.php');
	}
}

