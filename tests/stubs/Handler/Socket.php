<?php

class   Plop_Handler_Socket_Stub
extends Plop_Handler_Socket
{
    public function createSocketStub()
    {
        return parent::_createSocket();
    }

    public function sendStub($s)
    {
        return parent::_send($s);
    }

    public function makePickleStub(Plop_RecordInterface $record)
    {
        return parent::_makePickle($record);
    }

    public function emitStub(Plop_RecordInterface $record)
    {
        return parent::_emit($record);
    }

    public function writeStub($s)
    {
        return parent::_write($s);
    }

    public function handleErrorStub(
        Plop_RecordInterface    $record,
        Exception               $exception
    )
    {
        return parent::handleError($record, $exception);
    }

    public function getRetryTimeStub()
    {
        return $this->_retryTime;
    }
}

