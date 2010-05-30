<?php

namespace pheasant/mapper;

interface Mapper
{
	public function insert(DomainObject $object);

	public function update(DomainObject $object);

	public function delete(DomainObject $object);

	public function hydrate($array);
}
