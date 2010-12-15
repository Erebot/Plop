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

class Plop_BufferingFormatter
{
    static public $defaultFormatter = NULL;
    protected $_lineFmt;

    public function __construct($lineFmt = NULL)
    {
        if ($lineFmt)
            $this->_lineFmt = $lineFmt;
        else
            $this->_lineFmt = self::$defaultFormatter;
    }

    public function formatHeader($records)
    {
        return "";
    }

    public function formatFooter($records)
    {
        return "";
    }

    public function format($records)
    {
        $rv = "";
        if (count($records)) {
            $rv .= $this->formatHeader($records);
            foreach ($records as &$record) {
                $rv .= $this->_lineFmt->format($record);
            }
            unset($record);
            $rv .= $this->formatFooter($records);
        }
        return $rv;
    }
}

Plop_BufferingFormatter::$defaultFormatter = new Plop_Formatter();

