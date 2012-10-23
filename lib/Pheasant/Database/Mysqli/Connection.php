<?php

namespace Pheasant\Database\Mysqli;

use Pheasant\Database\Dsn;
use Pheasant\Database\FilterChain;

/**
 * A connection to a MySql database
 */
class Connection
{
	private
		$_dsn,
		$_link,
		$_charset,
		$_filter,
		$_sequencePool
		;

	public static
		$counter=0,
		$timer=0,
		$debug=false
		;

	/**
	 * Constructor
	 * @param string a database uri
	 */
	public function __construct(Dsn $dsn)
	{
		$this->_dsn = $dsn;
		$this->_filter = new FilterChain();
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
	 * Closes a connection
	 * @chainable
	 */
	public function close()
	{
		if(isset($this->_link))
			$this->_link->close();
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
			mysqli_report(MYSQLI_REPORT_OFF);

			if(!$this->_link = mysqli_init())
				throw new Exception("Mysql initialization failed");

			$this->_link->options(MYSQLI_INIT_COMMAND, 'SET NAMES '. $this->charset());
			$this->_link->options(MYSQLI_OPT_CONNECT_TIMEOUT, 5);

			@$this->_link->real_connect(
				$this->_dsn->host, $this->_dsn->user, $this->_dsn->pass,
				$this->_dsn->database, $this->_dsn->port
			);

			if ($this->_link->connect_error)
				throw new Exception("Failed to connect to mysql: {$this->_link->connect_error}", $this->_link->connect_errno);
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

		$mysqli = $this->_mysqli();
		$sql = count($params) ? $this->binder()->bind($sql, $params) : $sql;

		// delegate execution to the filter chain
		return $this->_filter->execute($sql, function($sql) use($mysqli) {

			\Pheasant\Database\Mysqli\Connection::$counter++;

			$timer = microtime(true);
			$r = $mysqli->query($sql, MYSQLI_STORE_RESULT);

			\Pheasant\Database\Mysqli\Connection::$timer += microtime(true)-$timer;

			if(\Pheasant\Database\Mysqli\Connection::$debug)
			{
				printf('<pre>Pheasant executed <code>%s</code> on thread #%d in %.2fms, returned %d rows</pre>',
					$sql, $mysqli->thread_id, (microtime(true)-$timer)*1000, is_object($r) ? $r->num_rows : 0);
			}

			if($mysqli->error)
				throw new Exception($mysqli->error, $mysqli->errno);

			return new ResultSet($mysqli, $r === true ? false : $r);
		});
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
		if(!isset($this->_sequencePool))
		{
			// use a seperate connection, ensures transaction rollback
			// doesn't clobber sequences
			$this->_sequencePool = new SequencePool(new self($this->_dsn));
		}

		return $this->_sequencePool;
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
