<?php

namespace Plop\Stub\Handler;

class TimedRotatingFile extends \Plop\Handler\TimedRotatingFile
{
    public function computeRolloverStub($currentTime)
    {
        return parent::computeRollover($currentTime);
    }

    public function shouldRolloverStub(\Plop\RecordInterface $record)
    {
        return parent::shouldRollover($record);
    }

    public function getFilesToDeleteStub()
    {
        return parent::getFilesToDelete();
    }

    public function doRolloverStub()
    {
        return parent::doRollover();
    }

    public function getWhenStub()
    {
        return $this->when;
    }

    public function getBackupCountStub()
    {
        return $this->backupCount;
    }

    public function getUTCStub()
    {
        return $this->utc;
    }

    public function getIntervalStub()
    {
        return $this->interval;
    }

    public function getSuffixStub()
    {
        return $this->suffix;
    }

    public function getExtMatchStub()
    {
        return $this->extMatch;
    }

    public function getDayOfWeekStub()
    {
        return $this->dayOfWeek;
    }

    public function getRolloverAtStub()
    {
        return $this->rolloverAt;
    }
}
