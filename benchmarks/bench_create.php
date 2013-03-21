<?php

require_once(__DIR__.'/common.php');

$num = isset($argv[1]) ? $argv[1] : 1000;

printf("creating %d test domain objects\n", $num);
benchmark($num, function() {
    $rel = new TestRelationship();
    $object = new TestObject();
    $object->TestRel = $rel;
    $rel->save();
    $object->save();
});

