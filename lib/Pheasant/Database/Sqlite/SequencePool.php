<?php

namespace Pheasant\Database\Sqlite;
use \Pheasant\Database\Transaction;

/**
 * A sequence pool that uses an in-memory array.
 */
class SequencePool
{
	private $_connection, $_startId;

	/**
	 * Constructor
	 */
	public function __construct($connection, $startId=1)
	{
		$this->_connection = $connection;
		$this->_startId = $startId;
	}

	/**
	 * Creates the sequence table if it doesn't exist
	 * @chainable
	 */
	public function initialize()
	{
		return $this;
	}

	/**
	 * Clears the sequence pool
	 * @chainable
	 */
	public function clear()
	{
		return $this;
	}

	/**
	 * Returns the next integer in the sequence
	 */
	public function next($sequence)
	{
		return 1;
	}
}

