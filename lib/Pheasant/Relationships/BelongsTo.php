<?php

namespace Pheasant\Relationships;

/**
 * A BelongsTo relationship represents the weak side of a 1->1 relationship. The
 * local entity has responsibility for the foreign key.
 *
 */
class BelongsTo extends HasOne
{
    /* (non-phpdoc)
     * @see RelationshipType::get()
     */
    public function get($object, $key)
    {
        if(($localValue = $object->{$this->local}) === null)
            return null;

        return $this->hydrate($this->query("{$this->foreign}=?", $localValue)
            ->execute()->row());
    }

    /* (non-phpdoc)
     * @see RelationshipType::set()
     */
    public function set($object, $key, $value)
    {
        $object->set($this->local, $value->{$this->foreign});
    }
}
