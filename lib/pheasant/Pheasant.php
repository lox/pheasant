<?php

namespace pheasant;

class Pheasant
{
	private static $_instance;

	/**
	 * Either overrides the internal PheasantInstance or returns the current one
	 * @return PheasantInstance
	 */
	public static function instance($instance=null)
	{
		if($instance)
			self::$_instance = $instance;
		else if(!$instance && !isset(self::$_instance))
			self::$_instance = new PheasantInstance();
		return self::$_instance;
	}

	/**
	 * Sets up a new pheasant instance with the provided connection dsn
	 * @void
	 */
	public static function setup($dsn)
	{
		$instance = new \pheasant\PheasantInstance();
		$instance->connectionManager()->addConnection('default', $dsn);
		self::instance($instance);
	}

	/**
	 * Delegates static calls to the internal {@link PheasantInstance} object
	 */
	static function __callStatic($method, $arguments)
	{
		if(!method_exists(self::instance(), $method))
			throw new \InvalidArgumentException("Instance doesn't implement $method()");

		return call_user_func_array(
			array(self::instance(), $method), $arguments);
	}
}

