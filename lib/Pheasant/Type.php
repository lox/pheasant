<?php

namespace Pheasant;

/**
 * A column type in domain object, corresponds to a database type
 */
interface Type
{
    /**
     * Convert from the database format to a PHP format
     * @return mixed
     */
    public function unmarshal($value);

    /**
     * Convert from a PHP format to a database format
     * @return mixed
     */
    public function marshal($value);

    /**
     * Gets the sql for defining the column
     * @return string
     */
    public function columnSql($column, $platform);

    /**
     * Gets the type options
     * @return Options
     */
    public function options();

}
