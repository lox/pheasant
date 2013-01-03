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
        $_onerror = array(),
        $_onresult = array()
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
     * Attach an results filter, gets called with the query, result and the
     * time taken, returns result
     * @chainable
     */
    public function onResult($callback)
    {
        $this->_onresult []= $callback;

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
        $this->_onresult = array();

        return $this;
    }

    /**
     * Executes the query through the internal filters and executor
     * @return result set of some sort
     */
    public function execute($sql, $executor)
    {
        foreach($this->_onquery as $callback)
            $sql = call_user_func($callback, $sql);

        try {
            $ts = count($this->_onresult) ? microtime(true) : NULL;
            $result = call_user_func($executor, $sql);

            foreach($this->_onresult as $callback)
                $result = call_user_func($callback, $sql, $result, $ts);

            return $result;
        } catch (\Exception $e) {
            foreach($this->_onerror as $callback)
                call_user_func($callback, $e);

            throw $e;
        }
    }
}
