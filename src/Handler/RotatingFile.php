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

namespace Plop\Handler;

/**
 *  \brief
 *      An handler the saves log messages in a file,
 *      which is rotated whenever it reaches a certain
 *      size.
 */
class RotatingFile extends \Plop\Handler\RotatingAbstract
{
    /// The maximum size the log file may reach before being rotated.
    protected $maxBytes;

    /// Number of backup log files to keep.
    protected $backupCount;

    /**
     * Construct a new instance of this handler.
     *
     * \param string $filename
     *      Name of the log file to write to.
     *
     * \param int $maxBytes
     *      The maximum size the log file may occupy, in bytes.
     *
     * \param int $backupCount
     *      (optional) Specifies how many backup logs are kept
     *      alongside the current log file.
     *      Backup logs are named after the date and time
     *      at which they were created. The exact format
     *      depends on the value of the \a $when parameter.
     *      The default value is 0, which disables deletion
     *      of old backups.
     *
     * \param string $mode
     *      (optional) Mode to use when opening
     *      the file. Defauts to "at" (append).
     *
     * \param bool $delay
     *      (optional) Whether to delay the actual
     *      opening of the file until the first write.
     *      Defaults to \a false (no delay).
     *
     * \note
     *      Depending on your installation, values bigger
     *      than 1\<\<31 (2 GB) for the \a $maxBytes parameter
     *      may not work correctly.
     */
    public function __construct(
        $filename,
        $maxBytes = 0,
        $backupCount = 0,
        $mode = 'a',
        $delay = 0
    ) {
        if ($maxBytes > 0) {
            $mode = 'a';
        }
        parent::__construct($filename, $mode, $delay);
        $this->maxBytes     = $maxBytes;
        $this->backupCount  = $backupCount;
    }

    /// \copydoc Plop::Handler::RotatingAbstract::doRollover().
    protected function doRollover()
    {
        fclose($this->stream);
        if ($this->backupCount > 0) {
            for ($i = $this->backupCount - 1; $i > 0; $i--) {
                $sfn = sprintf("%s.%d", $this->baseFilename, $i);
                $dfn = sprintf("%s.%d", $this->baseFilename, $i + 1);
                if (file_exists($sfn)) {
                    if (file_exists($dfn)) {
                        @unlink($dfn);
                    }
                    rename($sfn, $dfn);
                }
            }
            $dfn = sprintf("%s.1", $this->baseFilename);
            if (file_exists($dfn)) {
                @unlink($dfn);
            }
            rename($this->baseFilename, $dfn);
        }
        $this->mode     = 'w';
        $this->stream   = $this->open();
    }

    /// \copydoc Plop::Handler::RotatingAbstract::shouldRollover().
    protected function shouldRollover(\Plop\RecordInterface $record)
    {
        if (!$this->stream) {
            $this->stream = $this->open();
        }

        if ($this->maxBytes > 0) {
            $msg = $this->format($record)."\n";
            // The Python doc states this is due to a non-POSIX-compliant
            // behaviour under Windows.
            fseek($this->stream, 0, SEEK_END);
            $newPos = ftell($this->stream) + strlen($msg);
            if ($newPos >= $this->maxBytes || $newPos < 0) {
                return true;
            }
        }
        return false;
    }
}
