<?php

namespace Pheasant;
use \Pheasant;
use \Pheasant\PropertyReference;

/**
 * An object which represents an entity in the problem domain.
 */
class DomainObject implements \ArrayAccess
{
    private $_data = array();
    private $_changed = array();
    private $_saved = false;
    private $_overriden = array();
    private $_events;

    /**
     * The final constructer which initializes the object. Subclasses
     * can implement {@link construct()} instead
     */
    final public function __construct()
    {
        $pheasant = Pheasant::instance();
        $pheasant->initialize($this);

        // pull default values from schema
        $this->_data = $pheasant->schema($this)->defaults();

        // call user-defined constructor
        $constructor = method_exists($this,'construct')
            ? 'construct'
            : '_defaultConstruct'
            ;

        call_user_func_array(array($this,$constructor), func_get_args());
    }

    /**
     * Default method called without a constructor
     */
    protected function _defaultConstruct()
    {
        foreach(func_get_args() as $arg)
            if(is_array($arg)) $this->load($arg);
    }

    /**
     * Template function for configuring a domain object. Looks for either
     * a tableName() method or a mapper() method, a properties() method and a
     * relationships() method.
     */
    public static function initialize($builder, $pheasant)
    {
        $class = get_called_class();
        $instance = $class::fromArray(array());

        // register mappers and finders
        $pheasant->register($class, $instance->mapper());

        $builder
            ->properties($instance->properties())
            ->relationships($instance->relationships())
            ;
    }

    /**
     * Returns an Identity object for the domain object
     * @return Identity
     */
    public function identity()
    {
        return $this->schema()->identity($this);
    }

    /**
     * Returns whether the object has been saved
     * @return bool
     */
    public function isSaved()
    {
        return $this->_saved;
    }

    /**
     * Saves the domain object via the associated mapper
     * @chainable
     */
    public function save()
    {
        $event = $this->isSaved() ? 'Update' : 'Create';
        $mapper = Pheasant::instance()->mapperFor($this);

        $this->events()->wrap(array($event, 'Save'), $this, function($obj) use ($mapper) {
            $mapper->save($obj);

            // ensure we clear the changes before after events fire
            $obj->markSaved(true)->clearChanges();
        });

        return $this;
    }

    /**
     * Change the objects saved state
     * @chainable
     */
    public function markSaved($value=true)
    {
        $this->_saved = $value;

        return $this;
    }

    /**
     * Returns a key=>val array of properties that have changed since the last save
     * @return array
     */
    public function changes()
    {
        $changes = array();
        foreach(array_unique($this->_changed) as $key)
            $changes[$key] = $this->get($key, false);

        return $changes;
    }

    /**
     * Clears the changes array
     * @chainable
     */
    public function clearChanges()
    {
        $this->_changed = array();

        return $this;
    }

    /**
     * Deletes the domain object via the associated mapper
     * @chainable
     */
    public function delete()
    {
        $mapper = Pheasant::instance()->mapperFor($this);

        $this->events()->wrap(array('Delete'), $this, function($obj) use ($mapper) {
            $mapper->delete($obj);

            // ensure we clear the changes before after events fire
            $obj->markSaved(false)->clearChanges();
        });

        return $this;
    }

    /**
     * Returns the object as an array
     * @return array
     */
    public function toArray()
    {
        $array = array();

        foreach($this->_data as $key=>$value)
            $array[$key] = ($value instanceof PropertyReference) ? $value->value() : $value;

        return $array;
    }

    /**
     * Returns the Schema registered for this class.
     * @return Schema
    */
    public static function schema()
    {
        return Pheasant::instance()->schema(isset($this)
            ? $this : get_called_class());
    }

    /**
     * Creates and saves a domain object, var args are passed to the constructor
     * @return DomainObject
    */
    public static function create()
    {
        return self::schema()->newInstance(func_get_args())->save();
    }

    /**
     * Returns the connection object for the domain object.
     * This can be overridden on a per-class basis.
     * @return Connection
    */
    public static function connection()
    {
        return Pheasant::instance()->connection();
    }

    /**
     * Creates a transaction, passes the instance
     * @return Transaction
    */
    public function transaction($closure, $execute=true)
    {
        $transaction = static::connection()->transaction();
        $transaction->callback($closure, $this);

        if($execute)
            $transaction->execute();

        return $transaction;
    }

    /**
     * Creates a concurrency lock on the domain object, with an optional
     * closure to execute if the object has changed in the db when the lock
     * finally provided. Closure gets the original and the new object
     * @chainable
     */
    public function lock($onChanged=null)
    {
        $lock = new Locking\PessimisticLock($this);
        $locked = $lock->acquire();

        if (is_callable($onChanged) && !$locked->equals($this)) {
            call_user_func($onChanged, $this, $locked);
        }

        // reload data from locked
        $this->_data = $locked->_data;

        return $this;
    }

    // ----------------------------------------
    // template methods

    /**
     * Returns an array of Property objects
     */
    protected function properties()
    {
        return array();
    }

    /**
     * Returns an array of Relationship objects
     */
    protected function relationships()
    {
        return array();
    }

    /**
     * Returns an array of scope functions
     */
    public static function scopes()
    {
        return array();
    }

    /**
     * Returns the mapper for the object
     * @return Mapper
     */
    protected function mapper()
    {
        return new Pheasant\Mapper\RowMapper(
            $this->tableName(), static::connection());
    }

    /**
     * Used by the default initialize() method, returns the table name to use
     * @return string
     */
    protected function tableName()
    {
        $tokens = explode('\\', get_class($this));

        return strtolower(array_pop($tokens));
    }

    /**
     * Sets up the default internal event handlers
     */
    protected function _registerDefaultEventHandlers()
    {
        $this->events()
            ->register('*', array($this, 'eventHandler'))
            ;
    }

    // ----------------------------------------
    // event helper functions

    /**
     * Returns the domain objects event collection, optionally registering any passed
     * events
     * @return Events
     */
    public function events($events=array())
    {
        if (!isset($this->_events)) {
            $this->_events = new Events(array(), self::schema()->events());
            $this->_registerDefaultEventHandlers();
        }

        if(count($events))
            foreach($events as $event=>$callback)
                $this->_events->register($event, $callback);

        return $this->_events;
    }

    /**
     * Register a domain object to be saved after the current domain object is saved
     * @chainable
     */
    public function saveAfter($object)
    {
        $this->events()->register('afterSave', function() use ($object) {
            $object->save();
        });

        return $this;
    }

    /**
     * Handles events for the domain object, looks for local methods named after
     * the event, e.g beforeSave. The event is passed as a parameter
     * @return void
     */
    public function eventHandler($e, $obj)
    {
        if (method_exists($this, $e)) {
            call_user_func(array($this, $e), $e, $obj);
        }
    }

    // ----------------------------------------
    // cache functions

    /**
     * Defines a closure that is called when the property is accessed.
     * The closure is passed the property and the domain object.
     * @chainable
     */
    public function override($property, $closure)
    {
        $this->_overriden[$property] = $closure;
        return $this;
    }

    // ----------------------------------------
    // static helpers

    /**
     * Creates an instance from an array, bypassing the constructor and setters
     */
    public static function fromArray($array=array())
    {
        $className = get_called_class();

        // hack that uses object deserialization to bypass constructor
        $object = unserialize(sprintf('O:%d:"%s":0:{}',
            strlen($className),
            $className));

        $object->_data = $array;
        $object->_saved = true;

        return $object;
    }

    /**
     * Delegates find calls through to the finder
     */
    public static function __callStatic($method, $params)
    {
        if (preg_match('/^(find|all$|byId$|one)/',$method)) {
            return Finder\Wizard::fromClass(get_called_class())->dispatch($method, $params);
        } elseif (preg_match('/^(hasOne|hasMany|belongsTo)$/',$method)) {
            $refl = new \ReflectionClass('\Pheasant\\Relationships\\'.ucfirst($method));
            array_unshift($params, get_called_class());

            return $refl->newInstanceArgs($params);
        } else {
            throw new \BadMethodCallException("No static method $method available");
        }
    }

    /**
     * Creates and saves a array or arrays as domain objects
     * @return array of saved domain objects
     */
    public static function import($records)
    {
        $objects = array();
        $className = get_called_class();
        $defaults = self::schema()->defaults();

        foreach ($records as $record) {
            $objects []= $className::fromArray()
                ->load(array_merge($defaults, $record))->markSaved(false)->save();
        }

        return $objects;
    }

    /**
     * Return the class name of the domain object
     */
    public static function className()
    {
        return get_called_class();
    }

    // ----------------------------------------
    // container extension

    /**
     * Gets a property
     * @param string the property to get the value of
     * @return mixed
     */
    public function get($prop)
    {
        $value = isset($this->_data[$prop]) ? $this->_data[$prop] : null;

        // dereference property reference values
        return $value instanceof PropertyReference ? $value->value() : $value;
    }

    /**
     * Sets a property
     */
    public function set($prop, $value)
    {
        if (!isset($this->_data[$prop]) || $this->_data[$prop] !== $value) {
            $this->_data[$prop] = $value;
            $this->_changed[] = $prop;
        }

        return $this;
    }

    /**
     * Whether the object has a property, even if it's null
     */
    public function has($prop)
    {
        return array_key_exists($prop, $this->_data);
    }

    /**
     * Loads an array of values into the object
     * @param $filter only processes the keys listed, or false for all
     * @chainable
     */
    public function load($array, $filter=false)
    {
        // apply the optional filter
        if (is_array($filter)) {
            $array = array_intersect_key($array, array_fill_keys($filter, NULL));
        }

        foreach ($array as $key=>$value) {
            if(is_object($value) || is_array($value))
                $this->$key = $value;
            else
                $this->set($key, $value);
        }

        return $this;
    }

    /**
     * Compares the properties of one domain object to that of another
     */
    public function equals($object)
    {
        return $this->schema()->equals($this, $object);
    }

    /**
     * Returns keys that differ between the two objects
     */
    public function diff($object)
    {
        return $this->schema()->diff($this, $object);
    }

    /**
     * Reloads the contents of the object
     */
    public function reload()
    {
        $fresh = \Pheasant::instance()->finderFor($this)
            ->find($this->className(), $this->identity()->toCriteria())
            ->one()
            ;

        $this->_data = $fresh->_data;

        return $this;
    }

    // ----------------------------------------
    // object interface

    /**
     * Magic method, delegates to the schema for getters
     */
    public function __get($key)
    {
        if(isset($this->_overriden[$key])) {
            return call_user_func($this->_overriden[$key], $key, $this);
        }

        return call_user_func($this->schema()->getter($key), $this);
    }

    /**
     * Magic method, delegates to the schema for setters
     */
    public function __set($key, $value)
    {
        return call_user_func($this->schema()->setter($key), $this, $value);
    }

    /**
    * Magic method, delegates to the schema
    */
    public function __isset($key)
    {
        return ($this->schema()->hasAttribute($key) && $this->$key !== null);
    }

    /**
     * String coercion. Returns a value like "ClassName[pkcol1=foo,pkcol2=bar]"
     */
    public function __toString()
    {
        return (string) $this->identity();
    }

    // ----------------------------------------
    // array object

    public function offsetExists($offset)
    {
        return $this->schema()->hasAttribute($offset);
    }

    public function offsetGet($offset)
    {
        return $this->__get($offset);
    }

    public function offsetSet($offset, $value)
    {
        return $this->__set($offset, $value);
    }

    public function offsetUnset($offset)
    {
        throw new \BadMethodCallException("unset not supported on Pheasant\\DomainObject");
    }
}
