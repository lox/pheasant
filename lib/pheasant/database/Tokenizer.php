<?php

namespace pheasant\database;

/**
 * A simple tokenizer for ANSI '92 SQL
 */
class Tokenizer
{
	private $_tokens;
	private $_sql;

	public function __construct($sql)
	{
		$this->_sql = $sql;
	}

	/**
	 * @see http://search.cpan.org/~izut/SQL-Tokenizer-0.19/lib/SQL/Tokenizer.pm
	 * @see http://www.tehuber.com/article.php?story=20081016164856267
	 */
	public function tokenize()
	{
		$patterns = array(
			// inline comments
			'(?:--|\\#)[\\ \\t\\S]*',
			// logical operators
			'(?:<>|<=>|>=|<=|==|=|!=|!|<<|>>|<|>|\\|\\||\\||&&|&|-|\\+|\\*(?!\/)|\/(?!\\*)|\\%|~|\\^|\\?)',
			// empty single/double quotes
			'[\\[\\]\\(\\),;`]|\\\'\\\'(?!\\\')|\\"\\"(?!\\"")',
			// quoted strings
			'".*?(?:(?:""){1,}"|(?<!["\\\\])"(?!")|\\\\"{2})|\'.*?(?:(?:\'\'){1,}\'|(?<![\'\\\\])\'(?!\')|\\\\\'{2})',
			// c-style comments
			'\/\\*[\\ \\t\\n\\S]*?\\*\/',
			// words, placeholders
			'(?:[\\w:@]+(?:\\.(?:\\w+|\\*)?)*)',
			// whitespace
			'[\s]+',
			// punctuation
			'[\.]',
			);

		preg_match_all('/('.implode('|',$patterns).')/ims', $this->_sql, $matches);
		return $matches[0];
	}
}
