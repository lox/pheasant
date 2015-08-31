<?php

namespace Pheasant\Mapper;

use Pheasant;
use Pheasant\Collection;
use Pheasant\Query\Query;
use Pheasant\Query\Criteria;
use Pheasant\Finder\Finder;

/**
 * A generic mapper for mapping domain objects to rows in a table
 */
class RowMapper extends AbstractMapper implements Finder
{
    private $_table;
    private $_tableName;
    private $_pheasant;
    private $_connection;

    /**
     * Constructor
     */
    public function __construct($table, $connection=null, $pheasant=null)
    {
        $this->_pheasant = $pheasant ?: Pheasant::instance();
        $this->_connection = $connection ?: $this->_pheasant->connection();
        $this->_tableName = $table;
    }

    /**
     * Returns a table instance
     */
    public function table()
    {
        if(!isset($this->_table))
            $this->_table = $this->_connection->table($this->_tableName);

        return $this->_table;
    }

    /**
     * Generates a sequence name for a property
     * @return string
     */
    public function sequenceName($property)
    {
        $sequence = $property->type->options()->sequence;

        return $sequence ?: sprintf("%s_%s_seq", $this->_tableName, $property->name);
    }

    /**
     * Generates a sequence for a property
     * @return int
     */
    private function sequence($property)
    {
        return $this->_connection->sequencePool()
            ->next($this->sequenceName($property));
    }

    /**
     * @see AbstractMapper::insert()
     */
    protected function insert($object)
    {
        $schema = $object->schema();

        // generate any sequences that need generating
        foreach ($object->identity() as $key=>$property) {
            if(isset($property->type->options()->sequence))
                $object->set($key, $this->sequence($property));
        }

        // marshal, escape, insert
        $result = $this->table()
            ->insert($schema->marshal($object->toArray()));

        // check for auto-increment
        foreach ($schema->properties() as $key=>$property) {
            if($property->type->options()->auto_increment)
                $object->{$key} = $result->lastInsertId();
        }
    }

    /**
     * @see AbstractMapper::update()
     */
    protected function update($object, $changes)
    {
        $schema = $object->schema();
        $criteria = $object->identity()->toCriteria();

        if($criteria->isEmpty())
            throw new \InvalidArgumentException("Criteria is empty, refusing to update");

        $result = $this->table()->update($schema->marshal($changes),
            $criteria,
            $limit = 1
            );
    }

    /**
     * @see Mapper::delete()
     */
    public function delete($object)
    {
        if($object->isSaved())
            $this->table()->delete($object->identity()->toCriteria());
    }

    /* (non-phpdoc)
     * @see Mapper::query()
     */
    public function query(Criteria $criteria=null, $alias=null)
    {
        $query = new Query($this->_connection);

        if($alias) {
            $query->from('`'.$this->_tableName."` AS `".$alias."`");
        } else {
            $query->from('`'.$this->_tableName.'`');
        }

        // add optional where clause
        if($criteria) $query->where($criteria->toSql());

        return $query;
    }

    /* (non-phpdoc)
     * @see Finder::find()
     */
    public function find($class, Criteria $criteria=null)
    {
        return new Collection($class,
            $this->query($criteria, $this->_pheasant->schema($class)->alias()));
    }

    /* (non-phpdoc)
     * @see Mapper::initialize()
     */
    public function initialize($schema)
    {
        $migrator = new \Pheasant\Migrate\Migrator();
        $migrator->initialize($schema);
    }
}
