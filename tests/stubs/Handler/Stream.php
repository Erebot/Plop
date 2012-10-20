<?php

class   Plop_Handler_Stream_Stub
extends Plop_Handler_Stream
{
    public function emitStub(Plop_RecordInterface $record)
    {
        return parent::_emit($record);
    }
}

