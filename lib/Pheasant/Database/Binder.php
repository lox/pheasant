<?php

namespace Pheasant\Database;

/**
 * Encapsulates parameter binding and quoting
 */
class Binder
{
    /**
     * Interpolates quoted values in a string with ? in it.
     * @return string
     */
    public function bind($sql, array $params=array())
    {
        return $this->_bindInto('\?', $sql, $params, function($binder, $param) use ($sql) {
            return $binder->sqlValue($param);
        });
    }

    /**
     * Like bind(), but adds some magic:
     * - a=? becomes a IS NULL if passed null
     * - a!=? or a<>? becomes a IS NOT NULL if passed null
     * - a=? becomes a IN (1, 2, 3) if passed array(1, 2, 3)
     * - a!=? or a<>? becomes a NOT IN (1, 2, 3) if passed array(1, 2, 3)
     * @return string
     */
    public function magicBind($sql, array $params=array())
    {
        return $this->_bindInto('(?:`\w+`|\w+)\s*(?:!=|=|<>)\s*\?|\?', $sql, $params, function($binder, $param, $token) use ($sql) {
            if ($token == '?') {
                return $binder->sqlValue($param);
            } else {
                if(!preg_match("/^(.+?)(\s*(?:!=|=|<>)\s*)(.+?)$/", $token, $m))
                    throw new \InvalidArgumentException("Failed to parse magic token $token");

                $lhs = $m[1];
                $op = trim($m[2]);
                $rhs = $m[3];

                if(($op == '!=' || $op == '<>') && is_array($param)){
                    return $lhs . ' NOT IN ' . $binder->reduce($param);
                }
                if($op == '=' && is_array($param)) {
                    return $lhs . ' IN ' . $binder->reduce($param);
                }
                if(is_null($param)) {
                    return $lhs . ' IS'.($op == '=' ? '' : ' NOT').' NULL';
                }

                return $lhs . $m[2] . $binder->sqlValue($param);
            }
        });
    }

    /**
     * Escapes any characters not allowed in an SQL string
     * @return string
     */
    public function escape($string)
    {
        return is_string($string) ? addslashes($string) : $string;
    }

    /**
     * Surrounds a string with quote marks, null is returned as NULL, bools
     * converted to 1|empty string for compatibility
     * @return string
     */
    public function quote($string)
    {
        if(is_null($string)) {
            return 'NULL';
        }
        else if(is_bool($string)) {
            return $string === true ? 1 : "''";
        }
        else {
            return sprintf("'%s'", $string);
        }
    }

    /**
     * Intelligently quotes/escapes based on the provided type. If an object
     * is passed with a toSql() method, that is used, otherwise the php type
     * is used to infer what needs escaping.
     * @return string
     */
    public function sqlValue($mixed)
    {
        if(is_object($mixed) && method_exists($mixed, 'toSql')) {
            return $mixed->toSql($this);
        }

        return $this->quote($this->escape($mixed));
    }

    /**
     * Reduces an array of values into a bracketed, quoted, comma delimited list
     * @return string
     */
    public function reduce($array)
    {
        $tokens = array();

        foreach($array as $a){
            $tokens[] = $this->quote($this->escape($a));
        }

        return $tokens ? '('.implode(',', $tokens).')' : '(null)';
    }

    /**
     * Bind parameters into a particular pattern, skipping quoted strings which might have question marks
     * in them.
     */
    public function _bindInto($pattern, $sql, $params, $func)
    {
        $result = NULL;

        // http://stackoverflow.com/questions/5695240/php-regex-to-ignore-escaped-quotes-within-quotes
        // this could be done with back refs, but this is much faster
        $regex = "/('[^'\\\\]*(?:\\\\.[^'\\\\]*)*'|\"[^\"\\\\]*(?:\\\\.[^\"\\\\]*)*\"|$pattern)/";

        foreach (preg_split($regex, $sql, -1, PREG_SPLIT_DELIM_CAPTURE|PREG_SPLIT_NO_EMPTY) as $token) {
            if ($token == '?' || ($token[0] != '"' && $token[0] != "'" && preg_match("/$pattern/", $token))) {
                if(!count($params))
                    throw new \InvalidArgumentException("Not enough parameters to bind($sql)");

                $result .= $func($this, array_shift($params), $token);
            } else {
                $result .= $token;
            }
        }

        if (count($params)) {
            $exception = new \InvalidArgumentException("Parameters left over in bind($sql)");
            $exception->leftOverParams = $params;
            throw $exception;
        }

        return $result;
    }
}
