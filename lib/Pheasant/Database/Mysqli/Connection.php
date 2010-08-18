<?php

namespace Pheasant\Database\Mysqli;

/**
 * A connection to a MySql database
 */
class Connection
{
	private $_dsn;
	private $_link;

	/**
	 * Constructor
	 * @param string a database uri
	 */
	public function __construct($dsn)
	{
		$this->_dsn = $this->_parseDsn($dsn);
	}

	/**
	 * Parses a DSN and applies defaults
	 * @return object
	 */
	private function _parseDsn($dsn)
	{
		$array = parse_url($dsn);
		$params = array();

		if(isset($array['query']))
			parse_str($array['query'], $params);

		return (object) array_merge(array(
			'host'=>$array['host'],
			'user'=>$array['user'],
			'pass'=>$array['pass'],
			'database'=>basename($array['path']),
			'charset'=>'utf8',
			), $params);
	}

	/**
	 * The charset used by the database connection
	 * @return string
	 */
	public function charset()
	{
		return $this->_dsn->charset;
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

		if($params)
			$sql = $this->binder()->bind($sql, $params);

		if(!$result = $this->_mysqli()->query($sql, MYSQLI_STORE_RESULT))
			throw new Exception($this->_link->error, $this->_link->errno);

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
}
