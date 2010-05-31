<?php

namespace pheasant;

/**
 * A versioned key/value store that is optimized for accessing the
 * current values
 */
class Memento implements \IteratorAggregate
{
	private $_data=array();
	private $_revisions=array();
	private $_current;

	public function __construct($data=array())
	{
		if($data) $this->set($data);
	}

	public function toArray()
	{
		return (array) $this->_currentRevision();
	}

	/**
	 * Set an array of data into the memento in one version
	 */
	public function set($array)
	{
		$revision = $this->_nextRevision();

		foreach((array) $array as $key=>$value)
		{
			$revision->$key = $value;
			$this->_data[$key] = & $revision->{$key};
		}

		return $this;
	}

	// -----------------------------------------
	// versioning interface

	public function changesAfter($revision)
	{
		$count = count($this->_revisions);
		$changes = array();
		$previous = isset($this->_revisions[$revision-1])
			? (array) $this->_revisions[$revision-1]
			: array()
			;

		for($idx=$revision; $idx<$count; $idx++)
		{
			$current = (array) $this->_revisions[$idx];
			$changes = array_merge(
				$changes,
				array_keys(array_diff_assoc($previous,$current)),
				array_keys(array_diff_assoc($current,$previous))
				);

			$previous = $current;
		}

		return array_unique($changes);
	}

	public function revision($number)
	{
		return $this->_revisions[$number-1];
	}

	public function revisionNumber()
	{
		return count($this->_revisions);
	}

	private function _nextRevision()
	{
		$revision = new \stdClass();

		// clone previous revision values in the new one
		if(isset($this->_current))
			foreach($this->_current as $key=>$value)
			$revision->{$key} = $this->_current->{$key};

		$this->_revisions[] = $revision;
		$this->_current = $revision;
		return $revision;
	}

	private function _currentRevision()
	{
		if(!isset($this->_current))
			$this->_nextRevision();

		return $this->_current;
	}

	// -----------------------------------------
	// iterator interface

	public function getIterator()
	{
		return new ArrayIterator($this->toArray());
	}

	// -----------------------------------------
	// object interface

	public function __get($property)
	{
		return $this->_data[$property];
	}

	public function __set($property, $value)
	{
		$this->set(array($property=>$value));
	}

	public function __isset($property)
	{
		return isset($this->_data[$property]);
	}

	public function __unset($property)
	{
		$revision = $this->_nextRevision();
		unset($revision->{$property});
		unset($this->_data[$property]);
	}
}
