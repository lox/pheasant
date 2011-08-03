<?php

namespace Pheasant\Database\Mysqli;

use Pheasant\Database\Dsn;

/**
 * A connection to a MySql database
 */
class Connection
{
	private
		$_dsn,
		$_link,
		$_charset,
		$_filter
		;

	public $debug=false;

	/**
	 * Constructor
	 * @param string a database uri
	 */
	public function __construct($dsn)
	{
		$this->_dsn = is_string($dsn) ? new Dsn($dsn) : $dsn;
		$this->_charset = isset($this->_dsn->params['charset']) ?
		 	$this->_dsn->params['charset'] : 'utf8';	
	}

	/**
	 * The charset used by the database connection
	 * @return string
	 */
	public function charset()
	{
		return $this->_charset;
	}

	/**
	 * Lazily creates the internal mysqli object
	 * @return MySQLi
	 */
	private function _mysqli()
	{
		if(!isset($this->_link))
		{
			$this->_link = new \mysqli(
				$this->_dsn->host, $this->_dsn->user, $this->_dsn->pass, $this->_dsn->database, $this->_dsn->port);

			if ($this->_link->connect_error)
				throw new Exception($this->_link->connect_error, $this->_link->connect_errno);

			$this->execute('SET NAMES ?', $this->charset());
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

		$result = $this->_query($params
			? $this->binder()->bind($sql, $params) : $sql);

		return new ResultSet($this->_link, $result === true ? false : $result);
	}

	/**
	 * Executes an SQL query, outputs debugging if needed
	 * @return MySQLi_Result
	 */
	private function _query($sql)
	{
		if($this->debug)
			$timer = microtime(true);

		if(isset($this->_filter))
			$sql = call_user_func($this->_filter, $sql);

		$result = $this->_mysqli()->query($sql, MYSQLI_STORE_RESULT);

		if($this->debug)
		{
			if(php_sapi_name() != 'cli') printf("<pre>\n");
			printf("-------------------------------\n");
			printf("database: %s thread_id: %d\n", $this->_dsn->database, $this->_link->thread_id);
			printf("sql: %s\ntime: %.2fms\n", $sql, (microtime(true) - $timer) * 1000);
			printf($this->_link->info);
			if(php_sapi_name() != 'cli') printf("</pre>\n");
		}

		if(!$result)
			throw new Exception($this->_link->error, $this->_link->errno);

		return $result;
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
		return new Binder($this->_mysqli());
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
	 * Define a callback for filtering all queries
	 * @return string
	 */
	public function filterCallback($callback)
	{
		$this->_filter = $callback;
		return $this;
	}
}
