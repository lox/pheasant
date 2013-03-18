<?php

namespace Pheasant\Finder;

/**
 * An interface for finding collections of domain object
 */
interface Finder
{
    /**
     * Finds a collection of domain objects
     * @return Collection
     */
    public function find($class, \Pheasant\Query\Criteria $criteria=null);
}
