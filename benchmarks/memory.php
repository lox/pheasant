<?php

namespace Pheasant\Benchmark\Memory;

require_once(__DIR__.'/common.php');

use \Pheasant\Types;

\Pheasant::setup('mysql://pheasant:pheasant@localhost:/pheasanttest');

define('BENCHMARK_QTY', 1000);

class memory extends \Pheasant\DomainObject
{
    public static $destructs=0, $constructs=0;

    public function properties()
    {
        return array(
            'testid' => new Types\Sequence(),
            'blargh' => new Types\String(),
            'testrelid' => new Types\Integer(),
        );
    }

    public function relationships()
    {
        return array(
            'TestRel' => TestRel::belongsTo('testrelid')
            );
    }

    public function construct()
    {
        self::$constructs++;
    }

    public function __destruct()
    {
        self::$destructs++;
    }
}

class TestRel extends \Pheasant\DomainObject
{
    public function properties()
    {
        return array(
            'testrelid' => new Types\Sequence(),
        );
    }
}

// set up the database
$migrator = new \Pheasant\Migrate\Migrator();
$migrator->create('test', Test::schema());
$migrator->create('testrel', TestRel::schema());

\Pheasant::instance()->connection()->sequencePool()->initialize()->clear();

printf("creating %d test domain objects\n", BENCHMARK_QTY);
benchmark(BENCHMARK_QTY, function() {
    $rel = new TestRel();
    $object = new Test();
    $object->TestRel = $rel;
    $rel->save();
    $object->save();
});

printf("iterating over %d domain objects\n", BENCHMARK_QTY);

$memory = memory_get_usage(true);
$timestart = microtime(true);
$objects = Test::find();
$counter = 0;

printf("starting with %s bytes of memory used\n", number_format($memory));

foreach ($objects as $idx=>$object) {
    if($idx % 100 == 0)
        printf("iterating %d of %d\n", $idx, BENCHMARK_QTY);

    $counter++;
}

$elapsedMs = (microtime(true)-$timestart) * 1000;

printf("iterated over %d objects in in %.2fms (%.2f/ms)\n",
    $counter, $elapsedMs, $counter / $elapsedMs);

printf("ending with %s bytes of memory used\n", number_format($memory));

printf("used %s bytes of memory\n",
    number_format(memory_get_usage(true)-$memory));

printf("constructs: %d destructs: %d\n",
    Test::$constructs, Test::$destructs);
