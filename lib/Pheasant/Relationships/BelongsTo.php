<?php

namespace Pheasant\Relationships;

/**
 * A BelongsTo relationship represents the weak side of a 1->1 relationship. The
 * local entity has responsibility for the foreign key.
 *
 */
class BelongsTo extends HasOne
{
    private $_property;

    /* (non-phpdoc)
     * @see Relationship::get()
     */
    public function get($object, $key, $cache=null)
    {
        if ($cache) {
            $schema = \Pheasant::instance()->schema($this->class);

            if ($cached = $cache->get($schema->hash($object, array(array($this->local, $this->foreign))))) {
                return $schema->hydrate($cached);
            }
        }

        if (($localValue = $object->{$this->local}) === null) {
            return null;
        }

        return $this->hydrate($this->query("{$this->foreign}=?", $localValue)
            ->execute()->row());
    }

    /* (non-phpdoc)
     * @see Relationship::set()
     */
    public function set($object, $key, $value)
    {
        $object->set($this->local, $value->{$this->foreign});
    }
}
