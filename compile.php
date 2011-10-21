<?php

require_once __DIR__.'/autoload.php';

$compiler = new \Pheasant\Compiler();
$compiler->compile();

echo "built pheasant.phar\n";
