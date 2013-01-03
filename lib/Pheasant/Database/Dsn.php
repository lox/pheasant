<?php

namespace Pheasant\Database;

/**
 * Database connection string parser and generator
 */
class Dsn
{
    public
        $scheme,
        $host,
        $port=3306,
        $user,
        $pass,
        $database,
        $params=array()
        ;

    /**
     * Constructor
     */
    public function __construct($dsn)
    {
        $array = parse_url($dsn);

        if(isset($array['query']))
            parse_str($array['query'], $this->params);

        // process params that are named the same as props
        foreach (array('scheme','host','port','user','pass') as $p) {
            if(isset($array[$p]))
                $this->$p = $array[$p];
        }

        if(isset($array['path']))
            $this->database = basename($array['path']);
    }

    /**
     * Serialize the DSN into a string
     * @return string
     */
    public function __toString()
    {
        // user / password fragment
        if(isset($this->user) && isset($this->pass))
            $userpass = sprintf('%s:%s@', $this->user, $this->pass);
        else if(isset($this->user))
            $userpass = sprintf('%s@', $this->user);
        else
            $userpass = '';

        // database fragments
        $dbname = isset($this->database) ? "/{$this->database}" : "";

        // querystring fragments
        $qs = empty($this->params) ? "" : "?".http_build_query($this->params);

        return sprintf('%s://%s%s:%d%s%s',
            $this->scheme, $userpass, $this->host, $this->port, $dbname, $qs
        );
    }

    /**
     * Returns a clone with certain parameters changed
     */
    public function copy($alter=array())
    {
        $clone = clone $this;

        foreach($alter as $prop=>$value)
            $clone->$prop = $value;

        return $clone;
    }

    /**
     * Static constructor
     * @return Dsn
     */
    public static function fromString($dsn)
    {
        return new self($dsn);
    }
}
