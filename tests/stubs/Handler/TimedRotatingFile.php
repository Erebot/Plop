<?php
/*
    This file is part of Plop, a simple logging library for PHP.

    Copyright © 2010-2014 François Poirotte

    Plop is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    Plop is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with Plop.  If not, see <http://www.gnu.org/licenses/>.
*/

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
