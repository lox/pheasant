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
		if(count($params)==0)
			return $sql;

		$binder = $this;
		$function = function($m) use($binder, &$params) {
			if(!count($params))
				throw new \InvalidArgumentException("Not enough params passed to bind()");
			return $binder->quote($binder->escape(array_shift($params)));
		};

		return preg_replace_callback('/\?/', $function, $sql);
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
		if(count($params) == 0)
			return $sql;

		$binder = $this;
		$function = function($m) use($binder, &$params) {
			if(!count($params))
				throw new \InvalidArgumentException("Not enough params passed to magicBind()");

			$op = isset($m[3]) ? $m[3] : false;
			$param = array_shift($params);

			if(($op == '!=' || $op == '<>') && is_array($param))
				return ' NOT IN ' . $binder->reduce($param);

			if($op == '=' && is_array($param))
				return ' IN ' . $binder->reduce($param);

			if(is_null($param))
				return ' IS'.($op == '=' ? '' : ' NOT').' NULL';

			return (isset($m[1]) ? $m[1] : '') . $binder->quote($binder->escape($param));
		};

		return preg_replace_callback('/((\s*(!=|=|<>))?\s*)\?/', $function, $sql);
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
	 * converted to 1|0
	 * @return string
	 */
	public function quote($string)
	{
		if(is_null($string))
			return 'NULL';
		else if(is_bool($string))
			return $string === true ? 1 : 0;
		else
			return sprintf("'%s'", $string);
	}

	/**
	 * Reduces an array of values into a bracketed, quoted, comma delimited list
	 * @return string
	 */
	public function reduce($array)
	{
		$tokens = array();

		foreach($array as $a) 
			$tokens[] = $this->quote($this->escape($a));

		return '('.implode(',', $tokens).')';
	}
}
