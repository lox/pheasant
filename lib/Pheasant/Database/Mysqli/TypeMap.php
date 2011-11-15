<?php

namespace Pheasant\Database\Mysqli;

use \Pheasant\Types;

/**
 * A mapping between pheasant types and native db types
 */
class TypeMap
{
	private $_types;

	/**
	 * Constructor, takes a map of colname=>Type
	 */
	public function __construct($types)
	{
		$this->_types = $types;
	}

	/**
	 * @return string
	 */
	public function columnDef($colname)
	{
		$type = $this->_types[$colname];
		$options = $this->_nativeOptions($type);
		$length = $type->length;

		// null lengths don't have a width specifier
		$format = is_null($length) ? '`%s` %s' : '`%s` %s(%s)';

		switch($type->type)
		{
			case 'decimal':
				$length = sprintf('%d,%d',$type->length,$type->scale);
				break;
		}

		return sprintf($format,$colname,
			$this->_nativeType($type),
			$length).(strlen($options) ? " $options" : '');
	}

	/**
	 * @return array
	 */
	public function columnDefs()
	{
		$columns = array();

		foreach($this->_types as $type=>$def)
			$columns[] = $this->columnDef($type);

		return $columns;
	}

	/**
	 * Returns a native mysql column options for a {@link Type}
	 */
	private function _nativeOptions($type)
	{
		$opts = array();

		foreach($type->options as $key=>$value)
		{
			switch($key)
			{
				case 'primary':
					$opts [] = 'primary key';
					break;

				case 'required':
				case 'notnull':
					$opts [] = 'not null';
					break;

				case 'auto_increment':
					$opts []= $key;
					break;

				case 'default':
					$opts []= sprintf("default '%s'", $value);
					break;
			}
		}

		return implode(' ', $opts);
	}

	/**
	 * Returns a native mysql type for a {@link Type}
	 */
	private function _nativeType($type)
	{
		switch($type->type)
		{
			case 'string': return 'varchar';
			case 'integer': return 'int';
			case 'decimal': return 'decimal';
			case 'character': return 'char';
			case 'boolean' : return 'boolean';

			// fail if there is no match
			default: throw new Exception("Unknown type {$type->type}");
		}
	}
}
