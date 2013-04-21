<?php

namespace Pheasant\Query;

use \Pheasant;
use \Pheasant\Database\Binder;

/**
 * A builder object for simple sql where clauses. Magic is used to
 * provide chainable and() and or() methods for adding clauses
 */
class Criteria
{
    private $_sql='';

    /**
     * Constructor
     * @param $where either a query string, or a key=>val array
     * @param $params mixed, parameters to bind into the query string
     */
    public function __construct($where=null, $params=array())
    {
        if (is_object($where)) {
            $this->_sql = $where->toSql(false);
        } elseif (is_array($where)) {
            $conditions = array();
            foreach($where as $key=>$val)
                $conditions [] = $this->bind('`'.$key.'`'.'=?', array($val));

            $this->_sql = implode(' AND ', $conditions);
        } elseif (!empty($where)) {
            $this->_sql = $this->bind($where, (array) $params);
        }
    }

    /**
     * Binds an array of parameters into a string
     * @return string
     */
    public function bind($sql, $params=array())
    {
        $binder = new Binder();

        return $binder->magicBind($sql, (array) $params);
    }

    /**
     * Returns the sql representation of the where clause
     */
    public function toSql($braces=true)
    {
        return $braces ? "({$this->_sql})" : $this->_sql;
    }

    /**
     * Returns whether the criteria is empty
     */
    public function isEmpty()
    {
        return $this->toSql(false) == '';
    }

    public function __toString()
    {
        return $this->toSql();
    }

    /**
     * Triggers either the and() or or() methods
     */
    public function __call($method, $params)
    {
        $method = strtoupper($method);

        if($method != 'AND' && $method != 'OR')
            throw new \BadMethodCallException("Unknown method $method");

        if(!empty($this->_sql))
            $this->_sql = "($this->_sql) $method ";

        $this->_sql .= implode(" $method ", $params);

        return $this;
    }

    /**
     * Joins all parameters together with AND
     * @return Criteria
     */
    public static function concatAnd()
    {
        return new Criteria(implode(' AND ', func_get_args()));
    }

    /**
     * Joins all parameters together with OR
     * @return Criteria
     */
    public static function concatOr()
    {
        return new Criteria(implode(' OR ', func_get_args()));
    }
}
