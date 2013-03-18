<?php

namespace Pheasant\Database\Mysqli;

/**
 * A mysql transaction
 */
class Transaction
{
    private $_connection;
    private $_callbacks;
    public $results;

    /**
     * Constructor
     */
    public function __construct($connection=null)
    {
        $this->_connection = $connection ?: \Pheasant::instance()->connection();
    }

    public function execute()
    {
        if(count($this->_callbacks)==0)
            throw new Exception("No valid callbacks provided");

        $this->_connection->execute('BEGIN');
        $this->results = array();

        try {
            foreach ($this->_callbacks as $array) {
                list($callback, $arguments) = $array;
                $this->results[] = call_user_func_array($callback, $arguments);
            }

            $this->_connection->execute('COMMIT');

            return $this->results;
        } catch (\Exception $e) {
            $this->_connection->execute('ROLLBACK');
            throw $e;
        }
    }

    /**
     * Adds a callback
     * @chainable
     */
    public function callback($callback)
    {
        $arguments = array_slice(func_get_args(),1);
        $this->_callbacks[] = array($callback, $arguments);

        return $this;
    }

    public function __get($property)
    {
        if($property == 'results')

            return $this->_results;
        else
            throw \InvalidArgumentException(
                "No property called $property");
    }
}
