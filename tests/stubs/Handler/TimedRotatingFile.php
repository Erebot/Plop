<?php

class   Plop_Handler_TimedRotatingFile_Stub
extends Plop_Handler_TimedRotatingFile
{
    public function computeRolloverStub($currentTime)
    {
        return parent::_computeRollover($currentTime);
    }

    public function shouldRolloverStub(Plop_RecordInterface $record)
    {
        return parent::_shouldRollover($record);
    }

    public function getFilesToDeleteStub()
    {
        return parent::_getFilesToDelete();
    }

    public function doRolloverStub()
    {
        return parent::_doRollover();
    }

    public function getWhenStub()
    {
        return $this->_when;
    }

    public function getBackupCountStub()
    {
        return $this->_backupCount;
    }

    public function getUTCStub()
    {
        return $this->_utc;
    }

    public function getIntervalStub()
    {
        return $this->_interval;
    }

    public function getSuffixStub()
    {
        return $this->_suffix;
    }

    public function getExtMatchStub()
    {
        return $this->_extMatch;
    }

    public function getDayOfWeekStub()
    {
        return $this->_dayOfWeek;
    }

    public function getRolloverAtStub()
    {
        return $this->_rolloverAt;
    }
}

