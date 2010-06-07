<?php

namespace pheasant\mapper;

interface Mapper
{
	public function save($object);

	public function delete($object);

	public function find($sql=null, $params=array());

	public function hydrate($array);
}
