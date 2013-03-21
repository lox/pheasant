<?php

require_once(__DIR__.'/common.php');

// set up the database
$migrator = new \Pheasant\Migrate\Migrator();
$migrator->create('testobject', TestObject::schema());
$migrator->create('testrelationship', TestRelationship::schema());

$connection = \Pheasant::instance()->connection();

$connection->sequencePool()->initialize()->clear();
$connection->table('testobject')->truncate();
$connection->table('testrelationship')->truncate();


