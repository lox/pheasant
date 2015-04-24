<?php

namespace Pheasant\Mapper;

use \Pheasant\Query\Criteria;

/**
 * A persistence interface for a domain object
 */
interface Mapper
{
    /**
     * Perform any setup required for the mapper backend
     * @return void
     */
    public function initialize($schema);

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
    public function query(Criteria $criteria);
}
