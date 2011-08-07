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
	public function __construct(Dsn $dsn)
	{
		$this->_dsn = $dsn;
		$this->_charset = isset($this->_dsn->params['charset']) ?
		 	$this->_dsn->params['charset'] : 'utf8';	
	}

	/**
	 * Forces a connection, re-connects if already connected
	 * @chainable
	 */
	public function connect()
	{
		unset($this->_link);
		$this->_mysqli();
		return $this;
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
			if(!$this->_link = mysqli_init())
				throw new Exception("Mysql initialization failed");

			// this is per process in 5.3.4+
			mysqli_report(MYSQLI_REPORT_STRICT);

			$this->_link->options(MYSQLI_INIT_COMMAND, 'SET NAMES '. $this->charset());
			$this->_link->options(MYSQLI_OPT_CONNECT_TIMEOUT, 5);

			try
			{
				$this->_link->real_connect(
					$this->_dsn->host, $this->_dsn->user, $this->_dsn->pass, 
					$this->_dsn->database, $this->_dsn->port
				);
			}
			catch(\mysqli_sql_exception $e)
			{
				throw new Exception($e->getMessage(), $e->getCode());
			}
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
			printf("<pre>\n");
			printf("-------------------------------\n");
			printf("database: %s\n",$this->_dsn->database);
			printf("sql: %s\ntime: %.2fms\n",
				$sql, (microtime(true) - $timer) * 1000);

			if(is_object($result))
				printf("returned %d rows\n", $result->num_rows);
			printf("</pre>\n");
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
