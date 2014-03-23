<?php

namespace Pheasant;

class Relationship
{
    public $class, $local, $foreign;

    public function __construct($class, $local, $foreign=null)
    {
        $this->class = $class;
        $this->local = $local;
        $this->foreign = empty($foreign) ? $local : $foreign;
    }

    public function get($object, $key)
    {
        throw new \BadMethodCallException(
            "Get not supported on ".get_class($this));
    }

    public function set($object, $key, $value)
    {
        throw new \BadMethodCallException(
            "Set not supported on ".get_class($this));
    }

    public function add($object, $value)
    {
        throw new \BadMethodCallException(
            "Add not supported on ".get_class($this));
    }

    /**
     * Delegates to the finder for querying
     * @return Query
     */
    protected function query($sql, $params)
    {
        return \Pheasant::instance()->finderFor($this->class)
            ->query(new \Pheasant\Query\Criteria($sql, $params))
            ;
    }

    /**
     * Delegates to the schema for hydrating
     * @return DomainObject
     */
    protected function hydrate($row)
    {
        return \Pheasant::instance()
            ->schema($this->class)->hydrate($row);
    }

    /**
     * Helper function that creates a closure that calls the add function
     * @return Closure
     */
    protected function adder($object)
    {
        $rel = $this;

        return function($value) use ($object, $rel) {
            return $rel->add($object, $value);
        };
    }

    // -------------------------------------
    // delegate double dispatch calls to type

    public function getter($key, $cache=null)
    {
        $rel = $this;

        return function($object) use ($key, $rel, $cache) {
            return $rel->get($object, $key, $cache);
        };
    }

    public function setter($key)
    {
        $rel = $this;

        return function($object, $value) use ($key, $rel) {
            return $rel->set($object, $key, $value);
        };
    }

    // -------------------------------------
    // static helpers

    /**
     * Takes either a flat array of relationships or a nested key=>value array and returns
     * it as a nested format
     * @return array
     */
    public static function normalizeMap($array)
    {
        $nested = array();

        foreach ((array) $array as $key=>$value) {
            if (is_numeric($key)) {
                $nested[$value] = array();
            } else {
                $nested[$key] = $value;
            }
        }

        return $nested;
    }

    /**
     * Adds a join clause to the given query for the given schema and relationship. Optionally
     * takes a nested list of relationships that will be recursively joined as needed.
     * @return void
     */
    public static function addJoin($query, $parentAlias, $schema, $relName, $nested=array(), $joinType='inner')
    {
        if (!in_array($joinType, array('inner','left','right'))) {
            throw new \InvalidArgumentException("Unsupported join type: $joinType");
        }

        list($relName, $alias) = self::parseRelName($relName);
        $rel = $schema->relationship($relName);

        // look up schema and table for both sides of join
        $instance = \Pheasant::instance();
        $localTable = $instance->mapperFor($schema->className())->table();
        $remoteSchema = $instance->schema($rel->class);
        $remoteTable = $instance->mapperFor($rel->class)->table();

        $joinMethod = $joinType.'Join';
        $query->$joinMethod($remoteTable->name()->table, sprintf(
            'ON `%s`.`%s`=`%s`.`%s`',
            $parentAlias,
            $rel->local,
            $alias,
            $rel->foreign
            ),
            $alias
        );

        foreach (self::normalizeMap($nested) as $relName=>$nested) {
            self::addJoin($query, $alias, $remoteSchema, $relName, $nested, $joinType);
        }
    }

    /**
     * Parses `RelName r1` as array('RelName', 'r1') or `Relname` as array('RelName','RelName')
     * @return array
     */
    public static function parseRelName($relName)
    {
        $parts = explode(' ', $relName, 2);

        return isset($parts[1]) ? $parts : array($parts[0], $parts[0]);
    }
}
