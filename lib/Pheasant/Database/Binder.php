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

		list($sql,$placeholders,$quotes) = $this->_extractQuotedStrings($sql);

		$binder = $this;
		$function = function($m) use($binder, &$params) {
			if(!count($params))
				throw new \InvalidArgumentException("Not enough params passed to bind()");
			return $binder->quote($binder->escape(array_shift($params)));
		};

		$result = preg_replace_callback('/\?/', $function, $sql);

		if(count($placeholders))
			$result = str_replace($placeholders, $quotes, $result);

		return $result;
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

		list($sql,$placeholders,$quotes) = $this->_extractQuotedStrings($sql);

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

		$result = preg_replace_callback('/((\s*(!=|=|<>))?\s*)\?/', $function, $sql);

		if(count($placeholders))
			$result = str_replace($placeholders, $quotes, $result);

		return $result;
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
		if(is_null($string))
			return 'NULL';
		else if(is_bool($string))
			return $string === true ? 1 : "''";
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

	/**
	 * Extracts quoted strings from sql, replaces with placeholders.
	 * @return array
	 */
	private function _extractQuotedStrings($sql)
	{
		$result = NULL;
		$placeholders = array();
		$quotes = array();
		$lastChar = NULL;
		$buffer = NULL;
		$quoteStart = false;

		for($i=0; $i<strlen($sql); $i++)
		{
			$char = $sql[$i];

			// end an existing quote
			if($quoteStart !== false && $char == $quoteStart && $lastChar != '\\')
			{
				$quoteStart = false;
				$replacement = sprintf('[[[[P#%d]]]]', count($placeholders)+1);
				$result .= $replacement;
				$placeholders[] = $replacement;
				$quotes[] = $buffer.$char;
				$buffer = NULL;
			}
			// inside a quote
			else if($quoteStart !== false)
			{
				$buffer .= $char;
			}
			// start of a new quote
			else if($lastChar != '\\' && ($char == '"' || $char == "'" || $char == '`'))
			{
				$quoteStart = $char;
				$buffer .= $char;
			}
			else
			{
				$result .= $char;
			}

			$lastChar = $char;
		}

		// unmatched quotes
		if($quoteStart !== false)
			throw new \InvalidArgumentException("Unmatched quotes in string '$sql'");

		return array($result, $placeholders, $quotes);
	}
}
