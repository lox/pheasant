<?php

namespace Pheasant\Database\Mysqli;

/**
 * A Transaction Stack that keeps track of open savepoints
 */
class TransactionStack
{
    private
        $_transactionStack = array()
        ;

    /**
     * Get the depth of the stack
     * @return integer
     */
    public function depth()
    {
        return count($this->_transactionStack);
    }

    /**
     * Decend deeper into the transaction stack and return a unique
     * transaction savepoint name
     * @return string
     */
    public function descend()
    {
        $this->_transactionStack[] = current($this->_transactionStack) === false
            ? null
            : 'savepoint_'.$this->depth();

        return end($this->_transactionStack);
    }

    /**
     * Pop off the last savepoint
     * @return string
     */
    public function pop()
    {
      return array_pop($this->_transactionStack);
    }
}
