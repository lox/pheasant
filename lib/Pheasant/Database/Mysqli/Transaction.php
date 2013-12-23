<?php

namespace Pheasant\Database\Mysqli;

/**
 * A mysql transaction
 */
class Transaction
{
    private
        $_connection,
        $_events
        ;

    public $results;

    /**
     * Constructor
     */
    public function __construct($connection=null)
    {
        $this->_connection = $connection ?: \Pheasant::instance()->connection();
        $this->_events = new \Pheasant\Events();
    }

    public function execute()
    {
        $this->results = array();

        try {
            $this->_connection->execute('BEGIN');
            $this->_events->trigger('startTransaction', $this->_connection);
            $this->_connection->execute('COMMIT');
            $this->_events->trigger('commitTransaction', $this->_connection);
        } catch (\Exception $e) {
            $this->_connection->execute('ROLLBACK');
            $this->_events->trigger('rollbackTransaction', $this->_connection);
            throw $e;
        }

        return $this->results;
    }

    /**
     * Adds a callback that gets passed any extra varargs as a arguments
     * @chainable
     */
    public function callback($callback)
    {
        $t = $this;
        $args = array_slice(func_get_args(),1);

        // use an event handler to dispatch to the callback
        $this->_events->register('startTransaction', function($event, $connection) use ($t, $callback, $args) {
            $t->results []= call_user_func_array($callback, $args);
        });

        return $this;
    }

    /**
     * Get the events object
     * @return Events
     */
    public function events()
    {
        return $this->_events;
    }

    /**
     * Links another Events object such that events in it are corked until either commit/rollback and then uncorked
     * @chainable
     */
    public function deferEvents($events)
    {
        $this->_events
            ->register('startTransaction', function() use ($events) {
                $events->cork();
            })
            ->register('commitTransaction', function() use ($events) {
                $events->uncork();
            })
            ->register('rollbackTransaction', function() use ($events) {
                $events->discard()->uncork();
            })
            ;
    }
    /**
     * Creates a transaction and optionally execute a transaction
     * @return Transaction
    */
    public static function create($closure, $execute=true)
    {
        $transaction = new self();
        $transaction->callback($closure);

        if($execute)
            $transaction->execute();

        return $transaction;
    }
}
