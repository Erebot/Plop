<?php

namespace PEAR2\Plop\Handler;
use \PEAR2\Plop\Handler,
    \PEAR2\Plop\Record;

abstract class  BaseRotating
extends         Handler
{
    public function emit(Record &$record)
    {
        try {
            if ($this->shouldRollover($record))
                $this->doRollover();
            parent::emit($record);
        }
        catch (\Exception $e) {
            $this->handleError($record, $e);
        }
    }
}

