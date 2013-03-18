<?php

/**
 * Central object for object mapping and lookups, an instance is stored statically
 * in each domain object class
 */
class Pheasant
{
	private $_connections;
	private $_schema;
	private $_finders=array();
	private $_mappers=array();
	private $_mockloader;

	private static $_instance;

	/**
	 * Constructor
	 * @param $dsn string a database dsn
	 */
	public function __construct($dsn=null)
	{
		$this->_connections = new \Pheasant\Database\ConnectionManager();

		// the provided dsn is a default
		if($dsn) $this->_connections->addConnection('default', $dsn);
	}

	/**
	 * Returns a the connection manager
	 * @return object
	 */
	public function connections()
	{
		return $this->_connections;
	}

	/**
	 * Returns a connection by name
	 * @return object
	 */
	public function connection($name='default')
	{
		return $this->_connections->connection($name);
	}

	/**
	 * Initializes a domain objects schema if it has not yet been initialized
	 * @param $subject either an object or classname to initialize
	 * @param $callback a callback to call instead of the initialize method
	 * @return string the classname of the object
	 */
	public function initialize($subject, $callback=null)
	{
		$class = is_string($subject) ? $subject : get_class($subject);

		// initialize the object if needed
		if(!isset($this->_schema[$class]))
		{
			$builder = new \Pheasant\SchemaBuilder();
			$initializer = $callback ? $callback : $class.'::initialize';

			call_user_func($initializer, $builder, $this);
			$this->_schema[$class] = $builder->build($class);
		}

		return $class;
	}

	/**
	 * Gets the schema for an object, initializing it if needed.
	 * @param $subject mixed either object or classname
	 */
	public function schema($subject)
	{
		return $this->_schema[$this->initialize($subject)];
	}

	/**
	 * Register a mapper for a class, also registers the mapper as a finder
	 * @chainable
	 */
	public function register($class, $mapper)
	{
		return $this
			->registerMapper($class, $mapper)
			->registerFinder($class, $mapper)
			;
	}

	/**
	 * Registers the mapper for a class
	 * @chainable
	 */
	public function registerMapper($class, $mapper)
	{
		$this->_mappers[$class] = $mapper;
		return $this;
	}

	/**
	 * Registers the finder for a class
	 */
	public function registerFinder($class, $mapper)
	{
		$this->_finders[$class] = $mapper;
		return $this;
	}

	/**
	 * Looks up a mapper for either an object or a classname
	 * @return Mapper
	 */
	public function mapperFor($subject)
	{
		$class = $this->initialize($subject);

		if(!isset($this->_mappers[$class]))
			throw new Exception("No mapper registered for $class");

		return $this->_mappers[$class];
	}

	/**
	 * Looks up a finder for either an object or a classname
	 * @return Finder
	 */
	public function finderFor($subject)
	{
		$class = $this->initialize($subject);

		if(!isset($this->_finders[$class]))
			throw new Exception("No finder registered for $class");

		return $this->_finders[$class];
	}

	/**
	 * Register a callback to use to return a class whenever the 
	 * class is instantiated. This is done be prepending a class
	 * loader that dynamically the class, so it won't work if the
	 * class is already loaded.
	 * @chainable
	 */
	public function mock($class, $callback)
	{
		$this->mockloader()->mock($class, $callback);
		return $this;
	}

	/**
	 * Returns the internal MockLoader instance
	 */
	public function mockLoader()
	{
		if(!isset($this->_mockloader))
		{
			$this->_mockloader = new \Pheasant\MockLoader();
			$this->_mockloader->register();
		}

		return $this->_mockloader;
	}

	// ----------------------------------------
	// static accessors

	/**
	 * Shortcut for initializing the static pheasant instance
	 * @return Pheasant
	 */
	public static function setup($dsn=null)
	{
		return self::reset(new Pheasant($dsn));
	}

	/**
	 * Returns the static Pheasant instance
	 * @return Pheasant
	 */
	public static function instance()
	{
		return self::$_instance;
	}

	/**
	 * Resets the default static Pheasant instance
	 */
	public static function reset($instance)
	{
		return self::$_instance = $instance;
	}
}
