<?php

require_once(__DIR__.'/common.php');

$num = isset($argv[1]) ? $argv[1] : 10000;

$binder = new \Pheasant\Database\Binder();
$binds = array(
    array('SELECT * FROM table WHERE column=?', array('test')),
    array('x=?', array('10\'; DROP TABLE --')),
    array('column1=? and column2=?', array(false, true)),
    array("name='???' and llamas=?", array(24)),
    array("name='\'7r' and llamas=?", array(24)),
    array("name='\'7r\\\\' and another='test question?' and llamas=?", array(24)),
    array("name='\'7r\\\\' and x='\'7r' and llamas=?", array(24)),
);

printf("binding %d statements %d times\n", count($binds), $num);

benchmark($num, function() use($binds, $binder) {
    foreach($binds as $bind) {
        $binder->bind($bind[0], $bind[1]);
    }
});

