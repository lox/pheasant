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
		$_charset;

	public $debug=false;

	/**
	 * Constructor
	 * @param string a database uri
	 */
	public function __construct($dsn)
	{
		$this->_dsn = new Dsn($dsn);
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
				$this->_dsn->host, $this->_dsn->user, $this->_dsn->pass, $this->_dsn->database);

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

		$result = $this->_mysqli()->query($sql, MYSQLI_STORE_RESULT);

		if($this->debug)
		{
			printf("-------------------------------\n");
			printf("sql: %s\ntime: %.2fms\n",
				$sql, (microtime(true) - $timer) * 1000);

			if(is_object($result))
				printf("returned %d rows\n", $result->num_rows);
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
}
