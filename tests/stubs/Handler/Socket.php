<?php

namespace Plop\Stub\Handler;

class Socket extends \Plop\Handler\Socket
{
    public function createSocketStub()
    {
        return parent::createSocket();
    }

    public function sendStub($s)
    {
        return parent::send($s);
    }

    public function makePickleStub(\Plop\RecordInterface $record)
    {
        return parent::makePickle($record);
    }

    public function emitStub(\Plop\RecordInterface $record)
    {
        return parent::emit($record);
    }

    public function writeStub($s)
    {
        return parent::write($s);
    }

    public function handleErrorStub(
        \Plop\RecordInterface $record,
        \Exception $exception
    ) {
        return parent::handleError($record, $exception);
    }

    public function getRetryTimeStub()
    {
        return $this->retryTime;
    }
}
