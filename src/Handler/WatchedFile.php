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

namespace Plop\Handler;

/**
 *  \brief
 *      An handler that logs to a file and
 *      automatically reopens that file when
 *      it changes (eg. due to log rotation).
 */
class WatchedFile extends \Plop\Handler\File
{
    /// Device the watched file resides on.
    protected $dev;

    /// Inode number of the watched file.
    protected $ino;

    /// \copydoc Plop::Handler::File::__construct($filename, $mode, $delay).
    public function __construct(
        $filename,
        $mode = 'at',
        $delay = false
    ) {
        parent::__construct($filename, $mode, $delay);
        if (!file_exists($filename)) {
            $this->dev = $this->ino = -1;
        } else {
            $stats = stat($filename);
            $this->dev = $stats['dev'];
            $this->ino = $stats['ino'];
        }
    }

    /// \copydoc Plop::HandlerAbstract::emit().
    protected function emit(\Plop\RecordInterface $record)
    {
        if (!file_exists($this->baseFilename)) {
            $stats      = null;
            $changed    = true;
        } else {
            $stats = stat($this->baseFilename);
            $changed = (
                ($stat['dev'] != $this->dev) ||
                ($stat['ino'] != $this->ino)
            );
        }

        if ($changed && $this->stream !== false) {
            if (is_resource($this->stream)) {
                fflush($this->stream);
                fclose($this->stream);
            }
            $this->open();
            if (!$stats) {
                $stats = stat($this->baseFilename);
            }
            $this->dev = $stats['dev'];
            $this->ino = $stats['ino'];
        }
        parent::emit($record);
    }
}
