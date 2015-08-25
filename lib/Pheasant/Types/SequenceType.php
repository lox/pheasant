<?php

namespace Pheasant\Types;

class SequenceType extends IntegerType
{
    public $sequence;

    public function __construct($sequence=null, $params=null)
    {
        parent::__construct(11, sprintf("sequence=%s primary required %s",
            is_null($sequence) ? 'null' : $sequence, $params));

        $this->sequence = $sequence;
    }
}
