<?php

namespace Pheasant;

/**
 * A schema describes what a DomainObject can contain and how to access its attributes.
 */
class Schema
{
    private
        $_class,
        $_props=array(),
        $_rels=array(),
        $_getters=array(),
        $_setters=array(),
        $_events
        ;

    /**
     * Constructor
     * @param string the classname for the described domain object
     * @param array an array of parameters
     */
    public function __construct($class, $params=array())
    {
        $this->_class = $class;

        // split params into private props
        $this->_props = $params['properties'];
        $this->_rels = $params['relationships'];
        $this->_getters = $params['getters'];
        $this->_setters = $params['setters'];
        $this->_events = $params['events'];
    }

    /**
     * The name of the domain objectclass
     * @return string
     */
    public function className()
    {
        return $this->_class;
    }

    /**
     * A short alias for the object, used in queries
     * @return string
     */
    public function alias()
    {
        $fragments = explode("\\", str_replace('_', '\\', $this->_class));

        return end($fragments);
    }

    /**
     * Returns an identity for a domain object
     * @return Identity
     */
    public function identity($object)
    {
        return new Identity(get_class($object), $this->primary(), $object);
    }

    /**
     * Returns a hash in the form Class[key=val]
     */
    public function hash($object, $keys)
    {
        $keyValues = array_map(
            function ($k) use ($object) {
                $a = is_array($k);
                return sprintf('%s=%s',$a?$k[1]:$k, $object->{$a?$k[0]:$k});
            },
            $keys
        );

        return sprintf('%s[%s]', $this->_class, implode(',', $keyValues));
    }

    /**
     * Returns an array of Properties that form the primary keys
     * @return array
     */
    public function primary()
    {
        $columns = array_filter($this->_props, function($property) {
            return $property->type->options()->primary;
        });

        if(empty($columns))
            throw new Exception("No primary key defined for {$this->_class}");

        return $columns;
    }

    /**
     * Returns an array with properties with default values
     * @return array
     */
    public function defaults()
    {
        $defaults = array();

        foreach($this->_props as $key=>$prop)
            $defaults[$key] = $prop->defaultValue();

        return $defaults;
    }

    /**
     * Returns the Property objects for the schema
     * @return array
     */
    public function properties()
    {
        return $this->_props;
    }

    /**
     * Returns the Relationship objects for the schema
     * @return array
     */
    public function relationships()
    {
        return $this->_rels;
    }

    /**
     * Returns a particular Relationship
     * @return Relationship
     */
    public function relationship($name)
    {
        if(!isset($this->_rels[$name]))
            throw new \InvalidArgumentException("Unknown relationship $name");

        return $this->_rels[$name];
    }

    /**
     * Hydrates a database array into the domain object of the schema
     * @return object
     */
    public function hydrate($row)
    {
        $class = $this->_class;
        $ret = $class::fromArray($this->unmarshal($row));
        $ret->events()->trigger('afterHydrate', $ret);

        return $ret;
    }

    /**
     * Return the schema-wide {@link Events} object
     * @return Events
     */
    public function events()
    {
        return $this->_events;
    }

    /**
     * Converts an array using each columns type object to database format
     * @return array
     */
    public function marshal($row)
    {
        foreach ($this->_props as $key=>$prop) {
            if (isset($row[$key])) {
                $row[$key] = $prop->type->marshal($row[$key]);
            }
        }

        return $row;
    }

    /**
     * Converts an array using each columns type object to the object format
     * @return array
     */
    public function unmarshal($row)
    {
        foreach ($this->_props as $key=>$prop) {
            if (isset($row[$key])) {
                $row[$key] = $prop->type->unmarshal($row[$key]);
            }
        }

        return $row;
    }

    /**
     * Creates an instance of the object, passes args to the constructor
     * @return object
     */
    public function newInstance($args=array())
    {
        $refl = new \ReflectionClass($this->_class);

        return $refl->newInstanceArgs($args);
    }

    /**
     * Check if two objects have equal values as determined by their types
     */
    public function equals($o1, $o2)
    {
        // TODO: handle objects that don't match the schema
        foreach ($this->_props as $key=>$prop) {
            if(!$prop->type->equals($o1->get($key), $o2->get($key)))

                return false;
        }

        return true;
    }

    /**
     * Returns the keys that differ from the second object to the first by their types
     */
    public function diff($o1, $o2)
    {
        $diff = array();

        foreach ($this->_props as $key=>$prop) {
            if(!$prop->type->equals($o1->get($key), $o2->get($key)))
                $diff []= $key;
        }

        return $diff;
    }

    // ------------------------------------
    // route primitives to properties and relationships

    /**
     * Return a closure for getting an attribute from a domain object
     * @return closure
     */
    public function getter($attr)
    {
        if (isset($this->_getters[$attr])) {
            return $this->_getters[$attr];
        } elseif (isset($this->_props[$attr])) {
            return $this->_props[$attr]->getter($attr);
        } elseif (isset($this->_rels[$attr])) {
            return $this->_rels[$attr]->getter($attr);
        }

        throw new Exception("No getter available for $attr");
    }

    /**
     * Return a closure for setting an attribute on a domain object
     * @return closure
     */
    public function setter($attr)
    {
        if (isset($this->_setters[$attr])) {
            return $this->_setters[$attr];
        } elseif (isset($this->_props[$attr])) {
            return $this->_props[$attr]->setter($attr);
        } elseif (isset($this->_rels[$attr])) {
            return $this->_rels[$attr]->setter($attr);
        }

        throw new Exception("No setter available for $attr");
    }

    /**
    * Check if attribute is available on a domain object
    */
    public function hasAttribute($attr)
    {
        if (isset($this->_setters[$attr])) {
            return true;
        } elseif (isset($this->_props[$attr])) {
            return true;
        } elseif (isset($this->_rels[$attr])) {
            return true;
        }

        return false;
    }
}
