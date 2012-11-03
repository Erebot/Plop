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
 *      An handler that writes log messages
 *      to a PHP stream.
 *
 *  \see
 *      http://php.net/streams
 */
class   Plop_Handler_Stream
extends Plop_HandlerAbstract
{
    /// The stream where log messages will be write to.
    protected $_stream;

    /// A stream referencing \a STDERR.
    static protected $_stderr = NULL;

    /**
     * Create a new instance of this handler.
     *
     * \param resource $stream
     *      (optional) The stream where log messages
     *      will be written. Defaults to \a STDERR.
     */
    public function __construct($stream = NULL)
    {
        if ($stream === NULL) {
            if (self::$_stderr === NULL) {
                self::$_stderr = fopen('php://stderr', 'w');
            }
            $stream = self::$_stderr;
        }

        parent::__construct();
        $this->_stream      = $stream;
    }

    /**
     * Flush the stream's buffers.
     *
     * \return
     *      This method does not return any value.
     */
    protected function _flush()
    {
        fflush($this->_stream);
    }

    /// \copydoc Plop_HandlerAbstract::_emit().
    protected function _emit(Plop_RecordInterface $record)
    {
        $msg = $this->_format($record);
        fprintf($this->_stream, "%s\n", $msg);
        $this->_flush();
    }
}

