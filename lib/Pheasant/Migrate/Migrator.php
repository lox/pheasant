<?php

namespace Pheasant\Migrate;

/**
 * An automated migration tool for setting up database schemas derived from
 * domain object schemas.
 */
class Migrator
{
	private $_connection;

	/**
	 * Constructor
	 */
	public function __construct($connection=null)
	{
		$this->_connection = $connection ?: \Pheasant::instance()->connection();
	}

	/**
	 * Creates the underlying tables for a schema, dropping any tables of the same names
	 * @chainable
	 */
	public function create($table, $schema)
	{
		$columns = array();

		foreach($schema->properties() as $prop)
			$columns[$prop->name] = $prop->type;

		$table = $this->_connection->table($table);
		if($table->exists()) $table->drop();

		$table->create($columns);
		return $this;
	}
}
