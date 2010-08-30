<?php

namespace Pheasant\Mapper;

/**
 * A persistence interface for a domain object
 */
interface Mapper
{
	/**
	 * Saves a domain object, either creating it or updating it
	 * @return void
	 */
	public function save($object);

	/**
	 * Deletes a domain object
	 */
	public function delete($object);

	/**
	 * Returns a query object for querying the datasource
	 * @return Query
	 */
	public function query($sql, $params=array());
}
