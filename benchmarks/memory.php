<?php

namespace Pheasant\Benchmark\Memory;

require_once(__DIR__.'/../lib/Pheasant/ClassLoader.php');

$classloader = new \Pheasant\ClassLoader();
$classloader->register();

$memory1 = memory_get_usage(true);

\Pheasant::setup(
	'mysql://pheasant:pheasant@localhost:/pheasanttest?charset=utf8');

define('BENCHMARK_QTY', 1000);

class Test extends \Pheasant\DomainObject
{
	public static function configure($schema, $props, $rels)
	{
		$schema
			->table('test');

		$props
			->sequence('testid')
			->string('blargh')
			;
	}
}

// set up the database
$migrator = new \Pheasant\Migrate\Migrator();
$migrator->create(Test::schema());

printf("used %d bytes of memory in setup\n", memory_get_usage(true)-$memory1);

$memory2 = memory_get_usage(true);
$timestart = microtime(true);

printf("creating %d domain objects\n", BENCHMARK_QTY);

for($i=1; $i<=BENCHMARK_QTY; $i++)
{
	if($i % 100 == 0)
		printf("saving %d of %d\n",$i, BENCHMARK_QTY);

	$object = new Test();
	$object->blargh = 'blargh '.$i;
	$object->save();
}

$elapsedMs = (microtime(true)-$timestart) * 1000;

printf("created %d objects in in %.2fms (%.2f/ms)\n",
	BENCHMARK_QTY, $elapsedMs, BENCHMARK_QTY / $elapsedMs);

printf("used %s bytes of memory per object\n",
	number_format((memory_get_usage(true)-$memory2) / BENCHMARK_QTY));

$memory3 = memory_get_usage(true);
$timestart = microtime(true);

printf("iterating over %d domain objects\n", BENCHMARK_QTY);

$objects = Test::find();
$counter = 0;

foreach($objects as $idx=>$object)
{
	if($idx % 100 == 0)
		printf("iterating %d of %d\n", $idx, BENCHMARK_QTY);

	$counter++;
}

$elapsedMs = (microtime(true)-$timestart) * 1000;

printf("iterated over %d objects in in %.2fms (%.2f/ms)\n",
	$counter, $elapsedMs, $counter / $elapsedMs);

printf("used %s bytes of memory\n",
	number_format(memory_get_usage(true)-$memory3));
