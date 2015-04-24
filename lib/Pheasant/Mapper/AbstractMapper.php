<?php

namespace Pheasant\Mapper;

/**
 * A generic mapper object that provides infrastructure for other mappers
 */
abstract class AbstractMapper implements Mapper
{
    public function save($object)
    {
        if (!$object->isSaved()) {
            $this->insert($object);
        } elseif ($changes = $object->changes()) {
            $this->update($object, $changes);
        }

        return $this;
    }

    protected function insert($object)
    {
        throw new \BadMethodCallException(__FUNCTION__." not implemented");
    }

    protected function update($object, $changes)
    {
        throw new \BadMethodCallException(__FUNCTION__." not implemented");
    }

    public function delete($object)
    {
        throw new \BadMethodCallException(__FUNCTION__." not implemented");
    }
}
