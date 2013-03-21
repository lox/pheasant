<?php

require_once(__DIR__.'/common.php');

$num = isset($argv[1]) ? $argv[1] : 1000;

printf("iterating over %d domain objects\n", $num);

$memory = memory_get_usage(true);
$timestart = microtime(true);
$objects = TestObject::find()->limit($num);
$counter = 0;

printf("starting with %s bytes of memory used\n", number_format($memory));

foreach ($objects as $idx=>$object) {
    if($idx % 100 == 0)
        printf("iterating %d of %d\n", $idx, $num);

    $counter++;
}

$elapsedMs = (microtime(true)-$timestart) * 1000;

printf("iterated over %d objects in in %.2fms (%.2f/ms)\n",
    $counter, $elapsedMs, $counter / $elapsedMs);

printf("ending with %s bytes of memory used\n", number_format($memory));

printf("used %s bytes of memory\n",
    number_format(memory_get_usage(true)-$memory));

printf("constructs: %d destructs: %d\n",
    TestObject::$constructs, TestObject::$destructs);
