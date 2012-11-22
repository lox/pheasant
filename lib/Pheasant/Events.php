<?php

namespace Pheasant;

/**
 * Domain objects have events triggered on them via Events.
 */
class Events
{
  private $_handlers=array();

  /**
   * Construct
   */
  public function __construct($handlers=array())
  {
    $this->_handlers = array();

    foreach($handlers as $event=>$handler)
    {
      $this->_handlers[$event] = is_array($handler)
        ? $handler
        : array($handler)
        ;
    }
  }

  /**
   * Execute a closure, trigger a before_$event and after_$event
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
    foreach((array)$event as $e)
    {
      $callbacks = $this->_callbacksFor($e);

      foreach($callbacks as $callback)
        call_user_func($callback, $e, $object);
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
   * Clears either an event or all events
   * @chainable
   */
  public function clear($event=null)
  {
    if(!empty($event) && $event != '*')
      $this->_handlers[$event] = array();
    else
      $this->_handlers = array();

    return $this;
  }
}
