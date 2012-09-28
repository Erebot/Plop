<?php
/*
    This file is part of Plop, a simple logging library for PHP.

    Copyright © 2010-2012 François Poirotte

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

interface Plop_HandlerInterface
{
    public function getLevel();
    public function setLevel($level);

    public function handle(Plop_RecordInterface $record);

    public function getFormatter();
    public function setFormatter(Plop_FormatterInterface $formatter);

    public function handleError(
        Plop_RecordInterface $record,
        Exception            $exception
    );
}

