<?php

namespace Pheasant\Database\Mysqli;
use \Pheasant\Database\Transaction;

/**
 * A sequence pool that uses a single innodb table and row locks.
 */
class SequencePool
{
    const TABLE='sequences';
    private $_connection, $_startId;

    /**
     * Constructor
     */
    public function __construct($connection, $startId=1)
    {
        $this->_connection = $connection;
        $this->_startId = $startId;
    }

    /**
     * Creates the sequence table if it doesn't exist
     * @chainable
     */
    public function initialize()
    {
        $this->_connection->table(self::TABLE)
            ->createIfNotExists(array(
                'name' => new \Pheasant\Types\String(255, 'notnull primary'),
                'id' => new \Pheasant\Types\Integer(255, 'notnull unsigned'),
                ));

        return $this;
    }

    /**
     * Clears the sequence pool
     * @chainable
     */
    public function clear()
    {
        $this->_connection->table(self::TABLE)->truncate();

        return $this;
    }

    /**
     * Returns the next integer in the sequence
     */
    public function next($sequence)
    {
        // execute in transaction
        $results = $this->_connection
            ->transaction(array($this,'_nextSequence'), strtoupper($sequence))
            ->execute();

        return $results[0];
    }

    /**
     * Returns the current integer in the sequence
     */
    public function current($sequence)
    {
        $result = $this->_connection->execute(
            "SELECT id FROM sequences WHERE name=?", $sequence);

        return $result[0]['id'] - 1;
    }

    /**
     * Called within a transaction, gets the next sequence value
     * @access private
     */
    public function _nextSequence($sequence)
    {
        $sequence = strtoupper($sequence);
        $id = $this->_lockSequence($sequence);
        $increment = $this->_connection->execute(
            "UPDATE sequences SET id=id+1 WHERE name=?", $sequence);

        return $id;
    }

    /**
     * Locks the sequence, creates it if needed and returns the current value
     */
    private function _lockSequence($sequence)
    {
        $result = $this->_connection->execute(
            "SELECT id FROM sequences WHERE name=? FOR UPDATE", $sequence);

        switch (count($result)) {
            case 0:
                // sequence not in table; insert and use startId value
                $this->_connection->execute(
                    "INSERT INTO sequences VALUES (?,?)",
                    $sequence, $this->_startId);
                $id = $this->_startId;
                break;

            case 1:
                // sequence already in table; remember its current ID
                $id = $result[0]['id'];
                break;

            default:
                // multiple rows for sequence; bad
                throw new \Pheasant\Exception("Multiple rows exist for sequence '$sequence'");
                break;
        }

        return $id;
    }
}
