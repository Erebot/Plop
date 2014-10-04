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
