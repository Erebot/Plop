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
 *      An handler that writes log messages
 *      to a file.
 */
class File extends \Plop\Handler\Stream
{
    /// Path to the log file this handler writes to.
    protected $baseFilename;

    /// Opening mode for the log file.
    protected $mode;

    /**
     * Construct a new instance of this handler.
     *
     * \param string $filename
     *      Name of the log file to write to.
     *
     * \param string $mode
     *      (optional) Mode to use when opening
     *      the file. Defauts to "at" (append).
     *
     * \param bool $delay
     *      (optional) Whether to delay the actual
     *      opening of the file until the first write.
     *      Defaults to \a false (no delay).
     */
    public function __construct(
        $filename,
        $mode = 'at',
        $delay = false
    ) {
        $this->baseFilename = $filename;
        $this->mode         = $mode;
        if ($delay) {
            parent::__construct(false);
        } else {
            $stream = $this->open();
            parent::__construct($stream);
        }
    }

    /// Free the resources used by this handler.
    public function __destruct()
    {
        $this->close();
    }

    /**
     * Open the log file.
     *
     * \retval resource
     *      A stream representing the newly
     *      opened log file.
     */
    protected function open()
    {
        return fopen($this->baseFilename, $this->mode);
    }

    /// \copydoc Plop::HandlerAbstract::emit().
    protected function emit(\Plop\RecordInterface $record)
    {
        if (!$this->stream) {
            $this->stream = $this->open();
        }
        parent::emit($record);
    }

    /**
     * Close the log file.
     *
     * \return
     *      This method does not return any value.
     */
    protected function close()
    {
        if (is_resource($this->stream)) {
            $this->flush();
            fclose($this->stream);
        }
        $this->stream = false;
    }
}
