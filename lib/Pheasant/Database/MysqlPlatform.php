<?php

namespace Pheasant\Database;

class MysqlPlatform
{
    public function columnSql($column, $type, $options)
    {
        return trim(sprintf('`%s` %s %s', $column, $type, $this->_options($options)));
    }

    /**
     * Returns mysql column options for a given {@link Options}
     * @return string
     */
    private function _options($options)
    {
        $result = array();

        // certain parameters have to occur first
        if (isset($options->unsigned)) {
            $result []= 'unsigned';
        }

        if (isset($options->zerofill)) {
            $result []= 'zerofill';
        }

        foreach ($options as $key=>$value) {
            switch ($key) {
                case 'primary':
                    $result [] = 'primary key';
                    break;

                case 'required':
                case 'notnull':
                    $result [] = 'not null';
                    break;

                case 'default':
                    $result []= sprintf("default '%s'", $value);
                    break;

                case 'sequence':
                case 'unsigned':
                case 'zerofill':
                case 'allowed':
                    break;

                default:
                    $result []= $key;
                    break;
            }
        }

        return implode(' ', $result);
    }
}
