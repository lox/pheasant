<?php

define('BASEDIR', __DIR__.'/../');
define('LIBDIR', BASEDIR.'lib/');

// show all errors
error_reporting(E_ALL);

require_once(__DIR__.'/../vendor/autoload.php');
require_once(LIBDIR.'Pheasant/ClassLoader.php');

$classloader = new \Pheasant\ClassLoader();
$classloader->register();
