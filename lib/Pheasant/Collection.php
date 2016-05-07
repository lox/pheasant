<?php

namespace Pheasant;

use \Pheasant;
use \Pheasant\Query\QueryIterator;

class Collection implements \IteratorAggregate, \Countable, \ArrayAccess
{
    private
        $_query,
        $_iterator,
        $_scopes,
        $_add=false,
        $_readonly=false,
        $_schema,
        $_count,
        $_includes=array()
        ;

    /**
     * @param $class string the classname to hydrate
     * @param $query Query the query object
     * @param $add Closure a closure to call when an object is appended
     */
    public function __construct($class, $query, $add=false)
    {
        $this->_query = $query;
        $this->_add = $add;
        $this->_schema = $schema = $class::schema();
        $this->_iterator = new QueryIterator($this->_query, array($this,'hydrate'));
        $this->_scopes = $class::scopes();
    }

    /**
     * Hydrates a row to a DomainObject
     */
    public function hydrate($row)
    {
        $hydrated = $this->_schema->hydrate($row);

        // apply any eager-loaded includes
        foreach($this->_includes as $prop=>$includer) {
            $hydrated->override($prop, function($prop, $obj) use($includer) {
                return $includer->get($obj, $prop);
            });
        }

        return $hydrated;
    }

    /**
     * Return one and one only object from a collection
     * @throws ConstraintException if there are zero or >1 objects
     */
    public function one()
    {
        $object = $this->offsetGet(0);

        // execute after the query so we save a query
        $count = $this->count();
        if ($count === 0) {
            throw new NotFoundException("Expected 1 element, found 0");
        } elseif ($count > 1) {
            throw new ConstraintException("Expected only 1 element, found $count");
        }

        return $object;
    }

    public function last()
    {
        $last = $this->count()-1;

        if(!$this->offsetExists($last))
            throw new NotFoundException("No last element exist");

        return $this->offsetGet($last);
    }

    public function first()
    {
        if(!$this->offsetExists(0))
            throw new NotFoundException("No first element exist");

        return $this->offsetGet(0);
    }

    public function toArray()
    {
        return iterator_to_array($this->_iterator);
    }

    /**
     * Adds a filter to the collection
     * @chainable
     */
    public function filter($sql, $params=array())
    {
        $this->_queryForWrite()->andWhere($sql, $params);

        return $this;
    }

    /**
     * Orders the collection
     * @chainable
     * @deprecated
     */
    public function order($sql, $params=array())
    {
        return $this->orderBy($sql, $params);
    }

    /**
     * Orders the collection
     * @chainable
     */
    public function orderBy($sql, $params=array())
    {
        $this->_queryForWrite()->orderBy($sql, $params);

        return $this;
    }

    /**
     * Restricts the number of rows to return
     * @chainable
     */
    public function limit($rows, $offset=0)
    {
        $this->_queryForWrite()->limit($rows, $offset);

        return $this;
    }

    /**
     * Counts the number or results in the query
     */
    public function count()
    {
        if(!isset($this->_count))
            $this->_count = $this->_iterator->count();

        return $this->_count;
    }

    /**
     * Selects only particular fields
     * @chainable
     */
    public function select($fields)
    {
        $this->_queryForWrite()->select($fields);

        return $this;
    }

    /**
     * Reduces a collection down to a single column
     * @chainable
     */
    public function column($field)
    {
        $query = clone $this->_queryForWrite();

        return $query->select($field)->execute()->column($field);
    }

    /**
     * Creates the passed params as a domain object if there are no
     * results in the collection.
     * @param $args array an array to be passed to the constructor via call_user_func_array
     * @chainable
     */
    public function orCreate($args)
    {
        $query = clone $this->_queryForWrite();

        if(!$query->count())
            $this->_schema->newInstance(func_get_args())->save();

        return $this;
    }

    /**
     * Adds a locking clause to the query
     * @chainable
     */
    public function lock($clause=null)
    {
        $this->_queryForWrite()->lock($clause);

        return $this;
    }

    /**
     * Applies a callback to all objects in Collection, saves them if
     * the object has changed
     * @chainable
     */
    public function save($callback)
    {
        foreach ($this as $object) {
            call_user_func($callback, $object);
            if($object->changes()) $object->save();
        }

        return $this;
    }

    /**
     * Delete all objects in a collection
     * @chainable
     */
    public function delete()
    {
        // TODO: optimize this down into a single SQL call
        foreach ($this as $object) {
            $object->delete();
        }

        return $this;
    }

    /**
     * Returns an iterator
     */
    public function getIterator()
    {
        $this->_readonly = true;

        return $this->_iterator;
    }

    /**
     * Filter function when called as a function
     */
    public function __invoke($sql, $params=array())
    {
        return $this->filter($sql, $params);
    }

    /**
     * Gets the underlying query object
     */
    public function query()
    {
        return $this->_queryForWrite();
    }

    private function _queryForWrite()
    {
        if($this->_readonly)
            throw new Exception("Collection is read-only during iteration");

        return $this->_query;
    }

    /**
     * Return only unique combinations of the domain object in the collection, e.g such
     * as when you want only the unique objects that are the result of a join on
     * conditions. Only columns from the primary object will be in the result.
     * @chainable
     */
    public function unique()
    {
        $this->_queryForWrite()
            ->distinct()
            ->select($this->_schema->alias().".*")
            ;

        return $this;
    }

    /**
     * Join other related objects into a collection for the purpose of filtering. Relationships
     * is either a flat array of relationships (as defined in the object's schema) or a nested array
     * @chainable
     */
    public function join($rels, $joinType='inner')
    {
        $schemaAlias = $this->_schema->alias();

        foreach (Relationship::normalizeMap($rels) as $alias=>$nested) {
            Relationship::addJoin($this->_queryForWrite(),
                $schemaAlias, $this->_schema, $alias, $nested, $joinType);
        }

        return $this;
    }

    /**
     * Groups domain objects particular columns, either a single column an array. This
     * method is additive, it won't replace previous groupBy's
     * @chainable
     */
    public function groupBy($columns)
    {
        $this->_queryForWrite()->groupBy(implode(',', (array) $columns));

        return $this;
    }

    /**
     * Eager load relationships to avoid the N+1 problem
     * @chainable
     */
    public function includes($rels)
    {
        foreach (Relationship::normalizeMap($rels) as $alias=>$nested) {
            $this->_includes[$alias] = new Relationships\Includer(
                $this->_query, $this->_schema->relationship($alias), $nested
            );
        }

        return $this;
    }

    /**
     * Magic Method, used for scopes
     */
    public function __call($name, $args)
    {
        if(isset($this->_scopes[$name])) {
            array_unshift($args, $this);
            return call_user_func_array($this->_scopes[$name], $args);
        }

        throw new \BadMethodCallException("The method '$name' does not exist");
    }

    // ----------------------------------
    // helpers for common aggregate functions

    public function aggregate($function, $fields=null)
    {
        $query = clone $this->_query;

        return $query->select(sprintf('%s(%s)', $function, $fields))->execute()->scalar();
    }

    public function sum($field) { return $this->aggregate('SUM', $field); }
    public function max($field) { return $this->aggregate('MAX', $field); }
    public function min($field) { return $this->aggregate('MIN', $field); }
    public function avg($field) { return $this->aggregate('AVG', $field); }

    // ----------------------------------
    // array access

    public function offsetGet($offset)
    {
        $this->_iterator->seek($offset);

        return $this->_iterator->current();
    }

    public function offsetSet($offset, $value)
    {
        if(empty($this->_add) && is_null($offset))
            throw new \BadMethodCallException('Add not supported');
        else if(is_null($offset))
            return call_user_func($this->_add, $value);
        else
            throw new \BadMethodCallException('Set not supported');
    }

    public function offsetExists($offset)
    {
        try {
            $this->_iterator->seek($offset);
            return $this->_iterator->valid();
        } catch (\OutOfBoundsException $e) {
            return false;
        }
    }

    public function offsetUnset($offset)
    {
        if(!isset($this->_accessor))
            throw new \BadMethodCallException('Unset not supported');
    }
}
