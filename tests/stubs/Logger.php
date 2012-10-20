<?php

class   Plop_Logger_Stub
extends Plop_Logger
{
    public function callHandlersStub(Plop_RecordInterface $record)
    {
        return parent::_callHandlers($record);
    }

    public function emitWarningStub()
    {
        return parent::_emitWarning();
    }

    public function handleStub(Plop_RecordInterface $record)
    {
        return parent::_handle($record);
    }
}

