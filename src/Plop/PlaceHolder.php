<?php
/*
    This file is part of Plop.

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

class Plop_PlaceHolder
{
    public $loggerMap;

    public function __construct(Plop_Logger &$alogger)
    {
        $this->loggerMap = array($alogger);
    }

    public function append(Plop_Logger &$alogger)
    {
        $key = array_search($alogger, $this->loggerMap, TRUE);
        if ($key === FALSE)
            $this->loggerMap[] = $alogger;
    }
}

