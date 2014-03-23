<?php

namespace Pheasant;

/**
 * A cache for rows that are to be hydrated to objects
 */
interface Cache
{
    /**
     * @return bool
     */
    public function has($hash);

    /**
     * Gets a row from the cache, or returns false
     * @return array
     */
    public function get($hash);

    /**
     * Add or override a row in the cache. Expects a DomainObject
     */
    public function add($object);

    /**
     * Clears the entire cache
     */
    public function clear();
}
