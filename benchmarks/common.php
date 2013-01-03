<?php

require_once(__DIR__.'/../lib/Pheasant/ClassLoader.php');

$classloader = new \Pheasant\ClassLoader();
$classloader->register();

$memory_peak = 0;

declare(ticks = 35);
register_tick_function('log_peak_memory');

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
