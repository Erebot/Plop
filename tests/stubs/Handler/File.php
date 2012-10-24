<?php

class   Plop_Handler_File_Stub
extends Plop_Handler_File
{
    public function openStub()
    {
        return parent::_open();
    }

    public function closeStub()
    {
        return parent::_close();
    }

    public function emitStub(Plop_RecordInterface $record)
    {
        return parent::_emit($record);
    }

    public function getStreamStub()
    {
        return $this->_stream;
    }
}

