<?php

namespace Plop\Stub\Handler;

class SysLog extends \Plop\Handler\SysLog
{
    public function makeSocketStub()
    {
        return parent::makeSocket();
    }

    public function encodePriorityStub($facility, $priority)
    {
        return parent::encodePriority($facility, $priority);
    }

    public function closeStub()
    {
        return parent::close();
    }

    public function mapPriorityStub($levelName)
    {
        return parent::mapPriority($levelName);
    }

    public function emitStub(\Plop\RecordInterface $record)
    {
        return parent::emit($record);
    }

    public function getSocketStub()
    {
        return $this->socket;
    }

    public function getAddressStub()
    {
        return $this->address;
    }

    public function getFacilityStub()
    {
        return $this->facility;
    }
}
