<?php

namespace Pheasant;

/**
 * A schema describes what a DomainObject can contain and how to access it's attributes.
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
        $this->_events = new Events($params['events']);
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
     * Returns an identity for a domain object
     * @return Identity
     */
    public function identity($object)
    {
        return new Identity($this->primary(), $object);
    }

    /**
     * Returns an array of Properties that form the primary keys
     * @return array
     */
    public function primary()
    {
        return array_filter($this->_props, function($property) {
            return $property->type->options->primary;
        });

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
     * Hydrates an array into the domain object of the schema
     * @return object
     */
    public function hydrate($row, $saved=true)
    {
        $class = $this->_class;

        return $class::fromArray($row, $saved);
    }

    /**
     * Return the internal {@link Events} object
     * @return Events
     */
    public function events()
    {
        return $this->_events;
    }

    // ------------------------------------
    // route primitives to properties and relationships

    /**
     * Return a closure for getting an attribute from a domain object
     * @return closure
     */
    public function getter($attr)
    {
        if(isset($this->_getters[$attr]))

            return $this->_getters[$attr];

        else if(isset($this->_props[$attr]))
            return $this->_props[$attr]->getter($attr);

        else if(isset($this->_rels[$attr]))
            return $this->_rels[$attr]->getter($attr);

        throw new Exception("No getter available for $attr");
    }

    /**
     * Return a closure for setting an attribute on a domain object
     * @return closure
     */
    public function setter($attr)
    {
        if(isset($this->_setters[$attr]))

            return $this->_setters[$attr];

        else if(isset($this->_props[$attr]))
            return $this->_props[$attr]->setter($attr);

        else if(isset($this->_rels[$attr]))
            return $this->_rels[$attr]->setter($attr);

        throw new Exception("No setter available for $attr");
    }
}
