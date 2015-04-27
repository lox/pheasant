<?php

namespace Pheasant\Relationships;

use \Pheasant\Relationship;

/**
 * A BelongsTo relationship represents the weak side of a 1->1 relationship. The
 * local entity has responsibility for the foreign key.
 *
 */
class BelongsTo extends Relationship
{
    private $_property;
    private $_allowEmpty;

    /**
     * Constructor
     *
     */
    public function __construct($class, $local, $foreign=null, $allowEmpty=false)
    {
        parent::__construct($class, $local, $foreign);
        $this->_allowEmpty = $allowEmpty;
    }

    /* (non-phpdoc)
     * @see Relationship::get()
     */
    public function get($object, $key, $cache=null)
    {
        if ($cache) {
            $schema = \Pheasant::instance()->schema($this->class);

            if ($cached = $cache->get($schema->hash($object, array(array($this->local, $this->foreign))))) {
                return $cached;
            }
        }

        if (($localValue = $object->{$this->local}) === null) {
            if($this->_allowEmpty) {
                return null;
            } else {
                throw new \Pheasant\Exception("Local value is null while not allowed");
            }
        }

        $result = $this->query("{$this->foreign}=?", $localValue)->execute();

        if(!count($result)) {
            if($this->_allowEmpty) {
                return null;
            } else {
                throw new \Pheasant\Exception("Failed to find a {$key} (via {$this->foreign}={$localValue})");
            }
        }

        return $this->hydrate($result->row());
    }

    /* (non-phpdoc)
     * @see Relationship::set()
     */
    public function set($object, $key, $value)
    {
        $object->set($this->local, $value->{$this->foreign});
    }
}
