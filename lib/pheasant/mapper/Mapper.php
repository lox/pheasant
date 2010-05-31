<?php

namespace pheasant\mapper;

interface Mapper
{
	public function save($object);

	public function delete($object);
}
