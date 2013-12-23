<?php

namespace Pheasant\Migrate;

/**
 * An automated migration tool for setting up database schemas derived from
 * domain object schemas.
 */
class Migrator
{
    private $_pheasant;

    public function __construct($pheasant=null)
    {
        $this->_pheasant = $pheasant ?: \Pheasant::instance();
    }

    /**
     * Creates the underlying tables for a schema, dropping any tables of the same names
     * @deprecated use initialize
     * @chainable
     */
    public function create($table, $schema)
    {
        return $this
            ->destroy($schema, $table)
            ->initialize($schema, $table)
            ;
    }

    /**
     * Sets up an tables and sequences for a Schema
     * @chainable
     */
    public function initialize($schema, $table=null)
    {
        $mapper = $this->_mapper($schema);
        $table = $table ? $this->_connection($schema)->table($table) : $mapper->table();

        $sequencePool = $this->_connection($schema)->sequencePool();
        $sequencePool->initialize();

        $columns = array();

        // build a map of properties to create
        foreach ($schema->properties() as $prop) {
            $columns[$prop->name] = $prop->type;

            // reset sequences as we go
            if (property_exists($prop->type, 'sequence')) {
                $sequencePool->delete($mapper->sequenceName($prop));
            }
        }

        $table->create($columns);

        return $this;
    }

    /**
     * Destroy tables and sequences for a Schema
     */
    public function destroy($schema, $table=null)
    {
        $mapper = $this->_mapper($schema);
        $table = $table ? $this->_connection($schema)->table($table) : $mapper->table();

        $sequencePool = $this->_connection($schema)->sequencePool();
        $sequencePool->initialize();

        // delete any sequences
        foreach ($schema->properties() as $prop) {
            if (property_exists($prop->type, 'sequence')) {
                $sequencePool->delete($mapper->sequenceName($prop));
            }
        }

        // destory the table
        $table->dropIfExists();

        return $this;
    }

    private function _mapper($schema)
    {
        return $this->_pheasant->mapperFor($schema->className());
    }

    private function _connection($schema)
    {
        return call_user_func(array($schema->className(), 'connection'));
    }
}
