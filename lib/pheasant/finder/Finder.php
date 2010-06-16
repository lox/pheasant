<?php

namespace pheasant\finder;

/**
 * An interface for finding collections of domain object
 */
interface Finder
{
	/**
	 * Finds a collection of domain objects
	 * @return Collection
	 */
	public function find($sql=null, $params=array());
}
