<?php

namespace Pheasant\Database\Sqlite;

use Pheasant\Database\Dsn;
use Pheasant\Database\FilterChain;

/**
 * A connection to a Sqlite database
 */
class Connection
{
	private
		$_dsn,
		$_link,
		$_filter
		;

	/**
	 * Constructor
	 * @param string a database uri
	 */
	public function __construct(Dsn $dsn)
	{
		$this->_dsn = $dsn;
		$this->_filter = new FilterChain();
	}

	/**
	 * Forces a connection, re-connects if already connected
	 * @chainable
	 */
	public function connect()
	{
		unset($this->_link);
		$this->_sqlite();
		return $this;
	}

	/**
	 * The charset used by the database connection
	 * @return string
	 */
	public function charset()
	{
		return 'utf8';
	}

	/**
	 * Lazily creates the internal sqlite connection
	 * @return Sqlite3
	 */
	private function _sqlite()
	{
		if(!isset($this->_link))
		{
			$this->_link = new \SQLite3(':memory:');
		}

		return $this->_link;
	}

	/**
	 * Executes a statement
	 * @return ResultSet
	 */
	public function execute($sql, $params=array())
	{
		if(!is_array($params))
			$params = array_slice(func_get_args(),1);

		$sqlite = $this->_sqlite();
		$sql = count($params) ? $this->binder()->bind($sql, $params) : $sql;

		// delegate execution to the filter chain
		$result = $this->_filter->execute($sql, function($sql) use($sqlite) {
			$r = @$sqlite->query($sql);

			if(!$r)
				throw new Exception($sqlite->lastErrorMsg());

			return $r;
		});

		return new ResultSet($this->_link, $result === true ? false : $result);
	}

	/**
	 * @return Transaction
	 */
	public function transaction($callback=null)
	{
		$transaction = new Transaction($this);

		// optionally add a callback and any arguments
		if(func_num_args())
		{
			call_user_func_array(array($transaction,'callback'),
				func_get_args());
		}

		return $transaction;
	}

	/**
	 * @return Binder
	 */
	public function binder()
	{
		return new \Pheasant\Database\Binder();
	}

	/**
	 * @return Table
	 */
	public function table($name)
	{
		return new Table($name, $this);
	}

	/**
	 * @return SequencePool
	 */
	public function sequencePool()
	{
		return new SequencePool($this);
	}

	/**
	 * Takes a map of colName=>Type and returns map for the native connection
	 * @return TypeMap
	 */
	public function typeMap($array)
	{
		return new TypeMap($array);
	}

	/**
	 * Returns the internal filter chain 
	 * @return FilterChain 
	 */
	public function filterChain()
	{
		return $this->_filter;
	}
}

