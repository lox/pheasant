<?php

namespace Pheasant;

/**
 * An array-like structure that supports defaults and and numerically indexed keys.
 * Expands any options from int=>key to key=>$default
 */
class Options implements \IteratorAggregate
{
    private $_options=array();

    /**
     * Constructor
     */
    public function __construct($options, $default=true)
    {
        $this->merge($options, $default);
    }

    /**
     * Merges a new array into the options structure
     */
    public function merge($array, $default=true)
    {
        $newarray = array();

        foreach ($array as $key=>$value) {
            $newKey = is_numeric($key) ? $value : $key;
            $newValue = is_numeric($key) ? true : $value;
            $newarray[$newKey] = $newValue;
        }

        $this->_options = array_merge($this->_options, $newarray);

        return $this;
    }

    /* (non-phpdoc)
     * @see IteratorAggregate
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->_options);
    }

    public function __isset($key)
    {
        return array_key_exists($key, $this->_options);
    }

    public function __get($key)
    {
        return isset($this->_options[$key]) ? $this->_options[$key] : false;
    }

    /**
     * Creates an options object from a flat string
     * @return Options
     */
    public static function fromString($string, $default=true)
    {
        $array = array();

        foreach (preg_split('/\s+/', $string, -1, PREG_SPLIT_NO_EMPTY) as $token) {
            $fragments = explode("=", $token);
            $value = isset($fragments[1])
                ? trim($fragments[1],"' ") : $default;

            $array[$fragments[0]] = $value === 'null'
                ? null : $value;
        }

        return new self($array, $default);
    }

    /**
     * Serializes the options to a string.
     */
    public function toString($default=true)
    {
        $options = array();
        $binder = new Database\Binder();

        foreach ($this as $key=>$value) {
            $options[] = ($value === $default)
                ? $key
                : sprintf("%s=%s", $key, urlencode($value))
                ;
        }

        return implode(' ', $options);
    }

    /**
     * Convert either a null, a string or an array into an Option
     */
    public static function coerce($from)
    {
        if (is_array($from) || is_null($from)) {
            return new self((array) $from);
        } elseif (is_string($from)) {
            return self::fromString($from);
        } elseif ($from instanceof Options) {
            return $from;
        } else {
            throw new \InvalidArgumentException("Unable to coerce provided type");
        }
    }
}
