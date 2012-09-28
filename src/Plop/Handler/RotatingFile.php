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

class   Plop_Handler_RotatingFile
extends Plop_Handler_RotatingAbstract
{
    protected $_maxBytes;
    protected $_backupCount;

    public function __construct(
        $filename,
        $mode           = 'a',
        $maxBytes       = 0,
        $backupCount    = 0,
        $encoding       = NULL,
        $delay          = 0
    )
    {
        if ($maxBytes > 0) {
            $mode = 'a';
        }
        parent::__construct($filename, $mode, $encoding, $delay);
        $this->_maxBytes    = $maxBytes;
        $this->_backupCount = $backupCount;
    }

    protected function _doRollover()
    {
        fclose($this->_stream);
        if ($this->_backupCount > 0) {
            for ($i = $this->_backupCount - 1; $i > 0; $i--) {
                $sfn = sprintf("%s.%d", $this->_baseFilename, $i);
                $dfn = sprintf("%s.%d", $this->_baseFilename, $i + 1);
                if (file_exists($sfn)) {
                    if (file_exists($dfn)) {
                        @unlink($dfn);
                    }
                    rename($sfn, $dfn);
                }
            }
            $dfn = sprintf("%s.1", $this->_baseFilename);
            if (file_exists($dfn)) {
                @unlink($dfn);
            }
            rename($this->_baseFilename, $dfn);
        }
        $this->_mode    = 'w';
        $this->_stream  = $this->_open();
    }

    protected function _shouldRollover(Plop_RecordInterface $record)
    {
        if (!$this->_stream) {
            $this->_stream = $this->_open();
        }

        if ($this->_maxBytes > 0) {
            $msg = $this->_format($record)."\n";
            // The python doc states this is due to a non-POSIX-compliant
            // behaviour under Windows.
            fseek($this->_stream, 0, SEEK_END);
            if (ftell($this->_stream) + strlen($msg) >= $this->_maxBytes) {
                return TRUE;
            }
        }
        return FALSE;
    }
}

