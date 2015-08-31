<?php

require_once(__DIR__.'/../vendor/autoload.php');

use \Pheasant\Types;

$memory_peak = 0;

declare(ticks = 35);
register_tick_function('log_peak_memory');

Pheasant::setup('mysql://root@localhost:/pheasanttest');

/**
 * Log peak memory usage
 */
function log_peak_memory()
{
    global $memory_peak;

    if(($usage = memory_get_usage()) > $memory_peak)
        $memory_peak = $usage;
}

/**
 * Generic benchmarking function
 */
function benchmark($times, $callback)
{
    global $memory_peak;

    $counts = array();
    $memory = array();
    $params = array_slice(func_get_args(), 2);

    // warm-up the benchmark
    for($i=0; $i<5; $i++)
        call_user_func_array($callback, $params);

    // run it for real
    for ($i=0; $i<$times; $i++) {
        $timestart = microtime(true);
        $memstart = memory_get_usage();
        $memory_peak = 0;

        $result = call_user_func_array($callback, $params);
        $counts[] = microtime(true) - $timestart;
        $memory[] = $memory_peak - $memstart;
    }

    $totaltime = array_sum($counts);
    $avgtime = $totaltime / $times;
    printf("average time of %.2fms per iteration\n", $avgtime * 1000);

    $totalmem = array_sum($memory);
    $avgmem = $totalmem / $times;
    printf("average memory usage of %s bytes per iteration\n", number_format($avgmem));
}


class TestObject extends \Pheasant\DomainObject
{
    public static $destructs=0, $constructs=0;

    public function properties()
    {
        return array(
            'testid' => new Types\SequenceType(),
            'string1' => new Types\StringType(),
            'string2' => new Types\StringType(),
            'string3' => new Types\StringType(),
            'string4' => new Types\StringType(),
            'string5' => new Types\StringType(),
            'datetime1' => new Types\DateTimeType(),
            'datetime2' => new Types\DateTimeType(),
            'datetime3' => new Types\DateTimeType(),
            'int1' => new Types\IntegerType(),
            'int2' => new Types\IntegerType(),
            'int3' => new Types\IntegerType(),
            'int4' => new Types\IntegerType(),
            'int5' => new Types\IntegerType(),
            'testrelid' => new Types\IntegerType(),
        );
    }

    public function relationships()
    {
        return array(
            'TestRel' => TestRelationship::belongsTo('testrelid')
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

class TestRelationship extends \Pheasant\DomainObject
{
    public function properties()
    {
        return array(
            'testrelid' => new Types\SequenceType(),
        );
    }
}
