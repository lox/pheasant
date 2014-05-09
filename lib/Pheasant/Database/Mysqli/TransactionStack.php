<?php

namespace Pheasant\Database\Mysqli;

/**
 * A Transaction Stack that keeps track of open savepoints
 */
class TransactionStack
{
    private
        $_transactionStack
        ;

    public function __construct(){
        $this->_transactionStack = array();
    }

    public function count(){
        return count($this->_transactionStack);
    }

    public function descend(){
        $this->_transactionStack[] = current($this->_transactionStack) === false
            ? null
            : 'savepoint_'.count($this->_transactionStack);

        return end($this->_transactionStack);
    }

    public function pop(){
      return array_pop($this->_transactionStack);
    }
}
