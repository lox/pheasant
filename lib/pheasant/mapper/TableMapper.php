<?php

namespace pheasant\mapper;
use pheasant\Pheasant;

class TableMapper extends AbstractMapper
{
	private $_class;
	private $_connection;

	/**
	 * Constructor
	 */
	public function __construct($class, $connection=null)
	{
		$this->_class = $class;
		$this->_connection = $connection ?: Pheasant::connection();
	}

	/**
	 * Returns a table instance for a tablename
	 */
	private function table($schema)
	{
		return $this->_connection->table($schema->table());
	}

	/**
	 * Generates a sequence for a property
	 * @return int
	 */
	private function sequence($schema, $property)
	{
		$sequence = $property->sequence;

		// generate if needed
		if(!is_string($sequence))
			$sequence = sprintf("%s_%s_seq",
				$schema->table(), $property->name);

		return $this->_connection->sequencePool()->next($sequence);
	}

	/**
	 * @see GenericMapper::insert()
	 */
	protected function insert($object)
	{
		$schema = $object->schema();

		// generate any sequences that need generating
		foreach($object->identity()->properties() as $key=>$property)
		{
			if($property->sequence)
				$object->set($key, $this->sequence($schema, $property));
		}

		$result = $this->table($schema)->insert($object->changes());

		// check for auto-increment
		foreach($schema->properties()->primaryKeys() as $key=>$property)
		{
			if($property->auto_increment)
				$object->{$key} = $result->lastInsertId();
		}
	}

	/**
	 * @see GenericMapper::update()
	 */
	protected function update($object, $changes)
	{
		$schema = $object->schema();
		$result = $this->table($schema)->update($changes,
			$object->identity()->toArray());

		// check for auto-increment
		foreach($object->identity()->properties() as $key=>$property)
		{
			if($property->auto_increment)
				$object->{$key} = $result->lastInsertId();
		}
	}

	public function find($sql=null, $params=array())
	{
		$schema = Pheasant::schema($this->_class);
		$query = new \pheasant\query\Query();
		return $query
			->from($schema->table())
			->where($sql, $params)
			->execute()
			;
	}

	public function hydrate($object)
	{
	}
}
