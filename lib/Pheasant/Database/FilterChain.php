<?php

namespace Pheasant\Database;

/**
 * Provides a generic mechanism for filtering and observing execution of
 * an sql query
 */
class FilterChain
{
	private 
		$_onquery = array(), 
		$_onerror = array()
		;

	/**
	 * Attach an intercepting filter, gets called with query, returns query
	 * @chainable
	 */
	public function onQuery($callback)
	{
		$this->_onquery []= $callback;
		return $this;
	}	

	/**
	 * Attach an error handler, gets called with the exception, return ignored
	 * @chainable
	 */
	public function onError($callback)
	{
		$this->_onerror []= $callback;
		return $this;
	}

	/**
	 * Clears all callbacks
	 * @chainable
	 */
	public function clear()
	{
		$this->_onquery = array();
		$this->_onerror = array();
		return $this;
	}

	/**
	 * Executes the query through the internal filters and executor
	 * @return result set of some sort
	 */
	public function execute($sql, $executor)
	{
		foreach($this->_onquery as $callback)
			$sql = $callback($sql);

		try
		{
			return $executor($sql);
		}
		catch(\Exception $e)
		{
			foreach($this->_onerror as $callback)
				$callback($e);

			throw $e;
		}
	}
}
