<?php

abstract class  Plop_Handler_BaseRotating
extends         Plop_Handler_File
{
    public function emit(Plop_Record &$record)
    {
        try {
            if ($this->shouldRollover($record))
                $this->doRollover();
            parent::emit($record);
        }
        catch (Exception $e) {
            $this->handleError($record, $e);
        }
    }
}

