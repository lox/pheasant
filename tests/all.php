<?php

require_once('autorun.php');

class AllTests extends TestSuite
{
	function __construct()
	{
		parent::__construct('All tests');
		//$this->addFile(dirname(__FILE__).'/nodes.php');
		//$this->addFile(dirname(__FILE__).'/edges.php');
		//$this->addFile(dirname(__FILE__).'/traversal.php');
		//$this->addFile(dirname(__FILE__).'/indexes.php');
	}
}

