<?php

namespace Pheasant\Benchmark\Memory;

require_once(__DIR__.'/common.php');

\Pheasant::setup(
	'mysql://pheasant:pheasant@localhost:/pheasanttest?charset=utf8');

define('BENCHMARK_QTY', 1000);

class Test extends \Pheasant\DomainObject
{
	public static $destructs=0, $constructs=0;

	public static function initialize($builder, $pheasant)
	{
		$pheasant
			->register(__CLASS__, new \Pheasant\Mapper\RowMapper('test'));

		$builder
			->properties(array(
				'testid' => new \Pheasant\Types\Sequence(),
				'blargh' => new \Pheasant\Types\String(),
				));
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

// set up the database
$migrator = new \Pheasant\Migrate\Migrator();
$migrator->create('test', Test::schema());

printf("creating %d domain objects\n", BENCHMARK_QTY);
benchmark(BENCHMARK_QTY, function() {
	$object = new Test();
	$object->blargh = 'blargh';
	$object->save();
});

printf("iterating over %d domain objects\n", BENCHMARK_QTY);

$memory = memory_get_usage(true);
$timestart = microtime(true);
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
	number_format(memory_get_usage(true)-$memory));

printf("constructs: %d destructs: %d\n",
	Test::$constructs, Test::$destructs);
