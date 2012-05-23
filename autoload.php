<?php

require_once __DIR__.'/lib/Pheasant/ClassLoader.php';

function __pheasant_classloader_register()
{
	$classloader = new \Pheasant\ClassLoader();
	$classloader->register();
}

__pheasant_classloader_register();
