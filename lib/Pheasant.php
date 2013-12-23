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
    private $_events;

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
        $class = is_string($subject) ? ltrim($subject,'\\') : $subject::className();

        // initialize the object if needed
        if (!isset($this->_schema[$class])) {
            $builder = new \Pheasant\SchemaBuilder($this);
            $initializer = $callback ? $callback : $class.'::initialize';

            $this->events()->trigger('beforeInitialize', $builder);
            call_user_func($initializer, $builder, $this);
            $schema = $builder->build($class);

            $this->_schema[$class] = $schema;
            $this->events()->trigger('afterInitialize', $schema);
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
        $this->_mappers[ltrim($class,'\\')] = $mapper;

        return $this;
    }

    /**
     * Registers the finder for a class
     */
    public function registerFinder($class, $mapper)
    {
        $this->_finders[ltrim($class,'\\')] = $mapper;

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
     * Gets the system-wide events registry
     * @return Events
     */
    public function events()
    {
        if (!isset($this->_events)) {
            $this->_events = new \Pheasant\Events();
        }

        return $this->_events;
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

    // ----------------------------------------
    // static helpers

    /**
     * Creates a transaction, optionally executes
     * @return Transaction
    */
    public static function transaction($closure, $execute=true)
    {
        $transaction = self::instance()->connection()->transaction();
        $transaction->callback($closure);

        if($execute)
            $transaction->execute();

        return $transaction;
    }

}
