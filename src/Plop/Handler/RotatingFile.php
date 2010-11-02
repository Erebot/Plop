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

namespace PEAR2\Plop\Handler;
use \PEAR2\Plop\Record;

class   RotatingFile
extends BaseRotating
{
    public $maxBytes;
    public $backupCount;

    public function __construct(
        $filename,
        $mode           = 'a',
        $maxBytes       = 0,
        $backupCount    = 0,
        $encoding       = NULL,
        $delay          = 0
    )
    {
        if ($maxBytes > 0)
            $mode = 'a';
        parent::__construct($filename, $mode, $encoding, $delay);
        $this->maxBytes     = $maxBytes;
        $this->backupCount  = $backupCount;
    }

    public function doRollover()
    {
        fclose($this->_stream);
        if ($this->backupCount > 0) {
            for ($i = $this->backupCount - 1; $i > 0; $i--) {
                $sfn = sprintf("%s.%d", $this->baseFilename, $i);
                $dfn = sprintf("%s.%d", $this->baseFilename, $i + 1);
                if (file_exists($sfn)) {
                    if (file_exists($dfn))
                        @unlink($dfn);
                    rename($sfn, $dfn);
                }
            }
            $dfn = sprintf("%s.1", $this->baseFilename);
            if (file_exists($dfn))
                @unlink($dfn);
            rename($this->baseFilename, $dfn);
        }
        $this->mode     = 'w';
        $this->_stream  = $this->_open();
    }

    public function shouldRollover(Record &$record)
    {
        if (!$this->_stream)
            $this->_stream = $this->open();
        if ($this->maxBytes > 0) {
            $msg = $this->format($record)."\n";
            // The python doc states this is due to a non-POSIX-compliant
            // behaviour under Windows.
            fseek($this->_stream, 0, SEEK_END);
            if (ftell($this->_stream) + strlen($msg) >= $this->maxBytes)
                return TRUE;
        }
        return FALSE;
    }
}

