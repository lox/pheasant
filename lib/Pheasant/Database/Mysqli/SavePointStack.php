<?php

namespace Pheasant\Database\Mysqli;

/**
 * A Transaction Stack that keeps track of open savepoints
 */
class SavePointStack
{
    private
        $_savePointStack = array()
        ;

    /**
     * Get the depth of the stack
     * @return integer
     */
    public function depth()
    {
        return count($this->_savePointStack);
    }

    /**
     * Decend deeper into the transaction stack and return a unique
     * transaction savepoint name
     * @return string
     */
    public function descend()
    {
        $this->_savePointStack[] = current($this->_savePointStack) === false
            ? null
            : 'savepoint_'.$this->depth();

        return end($this->_savePointStack);
    }

    /**
     * Pop off the last savepoint
     * @return string
     */
    public function pop()
    {
      return array_pop($this->_savePointStack);
    }
}
