<?php

require_once(__DIR__.'/../vendor/simpletest/autorun.php');

class AllTests extends TestSuite
{
	function __construct()
	{
		parent::__construct('All tests');

		$exclude = array('all.php','base.php');

		// add all tests
		foreach(glob(dirname(__FILE__).'/*.php') as $file)
		{
			if(!in_array(basename($file), $exclude))
				$this->addFile($file);
		}
	}
}

