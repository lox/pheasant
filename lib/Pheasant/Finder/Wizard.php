<?php

namespace Pheasant\Finder;

use \Pheasant\Query\Criteria;

/**
 * Handles dispatching find, all and one methods to Finder objects,
 * provides magical finder methods via schema inspection
 */
class Wizard
{
    private $_class, $_schema, $_finder;

    /**
     * Construct
     */
    public function __construct($schema, $finder)
    {
        $this->_class = $schema->className();
        $this->_schema = $schema;
        $this->_finder = $finder;
    }

    /**
     * Delegates directory to the Finder::find method
     * @return Collection
     */
    public function find($criteria=null)
    {
        return $this->_finder->find($this->_class, $criteria);
    }

    /**
     * Magically derives a query to send to the internal finder
     * @return mixed Either a Collection or a DomainObject
     */
    public function dispatch($method, $params)
    {
        // find() and all() are aliases
        if (($method == 'find' && empty($params)) || $method == 'all') {
            return $this->find();
        }

        // handle find or one with sql params
        else if (($method == 'find' || $method == 'one') && is_string($params[0])) {
            $rs = $this->find(new Criteria(array_shift($params), $params));

            return $method == 'one' ? $rs->one() : $rs;
        }

        // handle magical finders
        else if (preg_match('/^(findBy|oneBy)/', $method)) {
            $rs = $this->find(new Criteria($this->_sqlFromMethod($method), $params));

            return preg_match('/^(oneBy)/', $method) ? $rs->one() : $rs;
        }

        // handle byId
        else if ($method == 'byId') {
            return $this->_findById($params);
        }

        // Criteria search
        else if (isset($params[0]) && $params[0] instanceof Criteria) {
            return $this->find($params[0]);
        }

        // failed to wizard :(
        else {
            throw new \BadMethodCallException("Unable to dispatch '$method' to finder");
        }
    }

    /**
     * Helper to build Wizard
     * @return Wizard
     */
    public static function fromClass($className)
    {
        return new self(
            \Pheasant::instance()->schema($className),
            \Pheasant::instance()->finderFor($className)
        );
    }

    /**
     * Find an object by primary key
     */
    private function _findById($params)
    {
        if(count($params) > 1)
            throw new \InvalidArgumentException("byId doesn't support composite keys");

        $keys = array_keys($this->_schema->primary());

        return $this->_finder->find($this->_class, new Criteria("`{$keys[0]}`=?", $params[0]))->one();
    }

    /**
     * Derives an sql clause from a finder method
     * e.g findByLlamaAndBlargh becomes llama=? and blargh=?
     */
    private function _sqlFromMethod($method)
    {
        if(!preg_match('/^(findBy|oneBy)(.*?)$/', $method, $m))
            throw new \BadMethodCallException("Unable to parse $method");

        // split on AND or OR and case boundries
        $sql = strtolower(preg_replace('/(?<=[a-z0-9\b])(Or|And)(?=[A-Z])/',' $1 ',$m[2]));

        // add parameter binds
        return preg_replace_callback('/\b([\w-]+)\b/', function($m) {
            return ($m[0] != 'or' && $m[0] != 'and') ? "`{$m[0]}`=?" : $m[0];
        }, $sql);
    }
}
