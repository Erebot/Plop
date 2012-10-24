<?php

class   Plop_Handler_SysLog_Stub
extends Plop_Handler_SysLog
{
    public function makeSocketStub()
    {
        return parent::_makeSocket();
    }

    public function encodePriorityStub($facility, $priority)
    {
        return parent::_encodePriority($facility, $priority);
    }

    public function closeStub()
    {
        return parent::_close();
    }

    public function mapPriorityStub($levelName)
    {
        return parent::_mapPriority($levelName);
    }

    public function emitStub(Plop_RecordInterface $record)
    {
        return parent::_emit($record);
    }

    public function getSocketStub()
    {
        return $this->_socket;
    }

    public function getAddressStub()
    {
        return $this->_address;
    }

    public function getFacilityStub()
    {
        return $this->_facility;
    }
}

