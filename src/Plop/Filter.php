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

class       Plop_Filter
implements  Plop_FilterInterface
{
    protected $_name;
    protected $_nlen;

    public function __construct($name = '')
    {
        $this->_name = $name;
        $this->_nlen = strlen($name);
    }

    public function filter(Plop_RecordInterface $record)
    {
        if (!$this->_nlen)
            return TRUE;

        if ($this->_name == $record['name'])
            return TRUE;

        if (strncmp($record['name'], $this->_name, $this->_nlen))
            return FALSE;

        return ($record['name'][$this->_nlen] == DIRECTORY_SEPARATOR);
    }
}

