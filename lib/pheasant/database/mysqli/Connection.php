<?php

namespace pheasant\database\mysqli;

class Connection
{
	private $_dsn;
	private $_link;

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

	public function charset()
	{
		return $this->_dsn->charset;
	}

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

	public function escape($string)
	{
		$escaper = new Escaper($this->_link);
		return $escaper->escape($string);
	}

	public function prepare($sql)
	{
		return new Statement($this, $sql);
	}

	public function execute($sql, $params=array())
	{
		if(!is_array($params))
			$params = array_slice(func_get_args(),1);

		$binder = new Binder($sql, $params, new Escaper($this->_link));

		if(!$result = $this->_mysqli()->query($binder, MYSQLI_STORE_RESULT))
			throw new Exception($this->_link->error, $this->_link->errno);

		return $result === true
			? new Result($this->_link)
			: new ResultSet($this->_link, $result)
			;
	}

	public function table($name)
	{
		return new Table($name, $this);
	}

	public function sequencePool()
	{
		return new SequencePool($this);
	}
}
