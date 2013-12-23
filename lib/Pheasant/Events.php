<?php

namespace Pheasant;

/**
 * A collection of event handlers that have events fired to them. Events
 * also bubble upstream.
 */
class Events
{
    private
        $_handlers = array(),
        $_queue = array(),
        $_corked = false,
        $_upstream
        ;

    /**
     * Construct
     */
    public function __construct($handlers=array(), $upstream=null)
    {
        $this->_handlers = array();
        $this->_upstream = $upstream;

        foreach ($handlers as $event=>$handler) {
            $this->_handlers[$event] = is_array($handler)
                ? $handler
                : array($handler)
                ;
        }
    }

    /**
     * Execute a closure, trigger a before{$event} and after{$event}
     * @chainable
     */
    public function wrap($event, $object, $callback)
    {
        $events = (array) $event;

        foreach($events as $e)
            $this->trigger("before{$e}", $object);

        call_user_func($callback, $object);

        foreach($events as $e)
            $this->trigger("after{$e}", $object);

        return $this;
    }

    /**
     * Triggers an event against the registered handlers
     * @chainable
     */
    public function trigger($event, $object)
    {
        if ($this->_corked) {
            $this->_queue []= func_get_args();
        } else {
            foreach ((array) $event as $e) {
                $callbacks = $this->_callbacksFor($e);

                foreach($callbacks as $callback)
                    call_user_func($callback, $e, $object);
            }

            if(isset($this->_upstream))
                $this->_upstream->trigger($event, $object);
        }

        return $this;
    }

    private function _callbacksFor($event)
    {
        $events = isset($this->_handlers[$event]) ? $this->_handlers[$event] : array();

        if(isset($this->_handlers['*']))
            $events = array_merge($events, $this->_handlers['*']);

        return $events;
    }

    /**
     * Registers a handler for an event
     * @chainable
     */
    public function register($event, $callback)
    {
        $this->_handlers[$event][] = $callback;

        return $this;
    }

    /**
     * Unregisters an event handler based on event, or all
     * @chainable
     */
    public function unregister($event=null)
    {
        if(!empty($event) && $event != '*')
            $this->_handlers[$event] = array();
        else
            $this->_handlers = array();

        return $this;
    }

    /**
     * Prevent events from firing until uncork() is called
     * @chainable
     */
    public function cork()
    {
        $this->_corked = true;

        return $this;
    }

    /**
     * Execute events that have been queued since cork() was called
     * @chainable
     */
    public function uncork()
    {
        $this->_corked = false;

        while ($call = array_shift($this->_queue)) {
            call_user_func_array(array($this,'trigger'), $call);
        }

        return $this;
    }

    /**
     * Discards any events queued with cork()
     * @chainable
     */
    public function discard()
    {
        $this->_queue = array();

        return $this;
    }

    /**
     * @see http://www.php.net/manual/en/language.oop5.magic.php#object.invoke
     */
    public function __invoke($event, $object)
    {
        return $this->trigger($event, $object);
    }
}
