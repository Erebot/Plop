<?php

class   Plop_Logger_Stub
extends Plop_Logger
{
    public function callHandlersStub(Plop_RecordInterface $record)
    {
        return parent::_callHandlers($record);
    }
}

