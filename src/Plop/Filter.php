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

namespace PEAR2\Plop;

class Filter
{
    public $name;
    public $nlen;

    public function __construct($name = '')
    {
        $this->name = $name;
        $this->nlen = strlen($name);
    }

    public function filter(Record $record)
    {
        if (!$this->nlen)
            return TRUE;

        if ($this->name == $record->dict['name'])
            return TRUE;

        if (!strncmp($record->dict['name'], $this->name, $this->nlen))
            return FALSE;

        return ($record->dict['name'][$this->nlen] == DIRECTORY_SEPARATOR);
    }
}

