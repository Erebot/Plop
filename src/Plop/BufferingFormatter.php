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

class Plop_BufferingFormatter
{
    protected $_lineFormat;

    public function __construct(Plop_FormatterInterface $lineFormat)
    {
        $this->_lineFormat = $lineFormat;
    }

    protected function _formatHeader($records)
    {
        return "";
    }

    protected function _formatFooter($records)
    {
        return "";
    }

    public function format(Plop_RecordInterface $record /* , ... */)
    {
        $records = func_get_args();
        foreach ($records as $record) {
            if (!($record instanceof Plop_RecordInterface))
                throw new Exception('Not a valid record');
        }
        $rv = "";
        if (count($records)) {
            $rv .= $this->_formatHeader($records);
            foreach ($records as $record) {
                $rv .= $this->_lineFormat->format($record);
            }
            $rv .= $this->_formatFooter($records);
        }
        return $rv;
    }
}

