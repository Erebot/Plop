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

/**
 *  \brief
 *      An handler that logs to a file and
 *      automatically reopens that file when
 *      it changes (eg. due to log rotation).
 */
class   Plop_Handler_WatchedFile
extends Plop_Handler_File
{
    /// Device the watched file resides on.
    protected $_dev;

    /// Inode number of the watched file.
    protected $_ino;

    /// \copydoc Plop_Handler_File::__construct($filename, $mode, $delay).
    public function __construct(
        $filename,
        $mode       = 'at',
        $delay      = FALSE
    )
    {
        parent::__construct($filename, $mode, $delay);
        if (!file_exists($filename)) {
            $this->_dev = $this->_ino = -1;
        }
        else {
            $stats = stat($filename);
            $this->_dev = $stats['dev'];
            $this->_ino = $stats['ino'];
        }
    }

    /// \copydoc Plop_HandlerAbstract::_emit().
    protected function _emit(Plop_RecordInterface $record)
    {
        if (!file_exists($this->_baseFilename)) {
            $stats = NULL;
            $changed = 1;
        }
        else {
            $stats = stat($this->_baseFilename);
            $changed = (
                ($stat['dev'] != $this->_dev) ||
                ($stat['ino'] != $this->_ino)
            );
        }
        if ($changed && $this->_stream !== FALSE) {
            if (is_resource($this->_stream)) {
                fflush($this->_stream);
                fclose($this->_stream);
            }
            $this->_open();
            if (!$stats)
                $stats = stat($this->_baseFilename);
            $this->_dev = $stats['dev'];
            $this->_ino = $stats['ino'];
        }
        parent::emit($record);
    }
}

