<?php

namespace Pheasant\Database;

/**
 * Manages named connections
 */
class ConnectionManager
{
    private $_connections=array();
    private $_drivers=array();
    private $_default;

    /**
     * Adds a named connection, with a string dsn
     * @chainable
     */
    public function addConnection($name, $dsn)
    {
        if(isset($this->_connections[$name]))
            throw new \Pheasant\Exception("Connection $name already exists");

        $this->_connections[$name] = $dsn;

        return $this;
    }

    /**
     * Sets what connection name is used when 'default' is looked up
     * @chainable
     */
    public function changeDefault($default)
    {
        $this->_default = $default;

        return $this;
    }

    /**
     * Returns a connection
     */
    public function connection($name)
    {
        if ($name == 'default' && isset($this->_default))
            $name = $this->_default;

        if(!isset($this->_connections[$name]))
            throw new \Pheasant\Exception("No connection called $name registered");

        $connection = $this->_connections[$name];

        // lazily build the connection
        if(is_string($connection))
            $connection = $this->_connections[$name] =
                $this->_buildConnection(new Dsn($connection));

        return $connection;
    }

    /**
     * Clears connections
     */
    public function clear()
    {
        unset($this->_connections);

        return $this;
    }

    /**
     * Adds a connection class to use for a specific scheme
     */
    public function addDriver($scheme, $class)
    {
        $this->_drivers[$scheme] = $class;

        return $this;
    }

    /**
     * Builds a connection object for a given Dsn
     * @return Connection
     */
    private function _buildConnection(Dsn $dsn)
    {
        if (isset($this->_drivers[$dsn->scheme])) {
            $driver = $this->_drivers[$dsn->scheme];

            return is_string($driver)
                ? new $driver($dsn) : call_user_func($driver, $dsn);
        }

        // check built in drivers
        switch ($dsn->scheme) {
            case 'mysql':
            case 'mysqli':
                return new Mysqli\Connection($dsn);
        }

        throw new \Pheasant\Exception("Unknown driver $driver");
    }
}
