<?php

namespace pheasant\migrate;

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
		$this->_connection = $connection ?: \Pheasant::connection();
	}

	/**
	 * Creates the underlying tables for a schema, dropping any tables of the same names
	 * @chainable
	 */
	public function create($schema)
	{
		foreach(func_get_args() as $schema)
		{
			$this->table($schema)->create();
		}

		return $this;
	}

	public function table($schema)
	{
		$table = $this->_connection->table($schema->table());

		foreach($schema->properties() as $property)
		{
			$options = array();
			$type = $property->type;

			foreach(array('auto_increment','primary') as $key)
				if($property->$key) $options[] = $key;

			$options['notnull'] = $property->required;

			$table->$type($property->name, $property->length, $options);
		}

		return $table;
	}
}
