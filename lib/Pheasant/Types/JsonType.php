<?php

namespace Pheasant\Types;

use Pheasant\Exception;

/**
 * A JSON type
 * @see https://dev.mysql.com/doc/refman/5.7/en/json.html
 */
class JsonType extends BaseType
{
    /* (non-phpdoc)
     * @see \Pheasant\Type::columnSql
     */
    public function columnSql($column, $platform)
    {
        return $platform->columnSql($column, 'json', $this->options());
    }

    /* (non-phpdoc)
     * @see \Pheasant\Type::unmarshal
     */
    public function unmarshal($value)
    {
        $value = json_decode($value);
        
        if (json_last_error()) {
            throw new Exception('Could not unmarshal json: ' . json_last_error_msg());
        }
        
        return $value;
    }

    /* (non-phpdoc)
     * @see \Pheasant\Type::marshal
     */
    public function marshal($value)
    {
        $value = json_encode($value);
        
        if (json_last_error()) {
            throw new Exception('Could not marshal json: ' . json_last_error_msg());
        }
        
        return $value;
    }
}
