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
 *      An abstract class that can be used as a base
 *      to create new log records handlers.
 *
 *  Subclasses must implement the _emit() method.
 */
abstract class  Plop_HandlerAbstract
extends         Plop_Filterer
implements      Plop_HandlerInterface
{
    /// Minimal level for log records this handler accepts to handle.
    protected $_level;

    /// Formatter object to use for this handler.
    protected $_formatter;

    /**
     * Create a new handler that accepts any log record.
     *
     * \param Plop_FormatterInterface $formatter
     *      (optional) The formatter this handler will use
     *      to render records. By default, a new instance
     *      of Plop_Formatter is created.
     */
    public function __construct(Plop_FormatterInterface $formatter = NULL)
    {
        parent::__construct();
        if ($formatter === NULL) {
            $formatter = new Plop_Formatter();
        }
        $this->setLevel(Plop::NOTSET);
        $this->setFormatter($formatter);
    }

    /// \copydoc Plop_HandlerInterface::getLevel().
    public function getLevel()
    {
        return $this->_level;
    }

    /// \copydoc Plop_HandlerInterface::setLevel().
    public function setLevel($level)
    {
        $this->_level = $level;
        return $this;
    }

    /// \copydoc Plop_HandlerInterface::getFormatter().
    public function getFormatter()
    {
        return $this->_formatter;
    }

    /// \copydoc Plop_HandlerInterface::setFormatter().
    public function setFormatter(Plop_FormatterInterface $formatter)
    {
        $this->_formatter = $formatter;
        return $this;
    }

    /**
     * Format a record.
     *
     * \param Plop_RecordInterface $record
     *      The record to format.
     *
     * \retval string
     *      Formatted representation of the record.
     */
    protected function _format(Plop_RecordInterface $record)
    {
        return $this->_formatter->format($record);
    }

    /**
     * Send the log to its final destination.
     *
     * \param Plop_RecordInterface $record
     *      The record to log.
     *
     * \return
     *      This method does not return any value.
     */
    abstract protected function _emit(Plop_RecordInterface $record);

    /// \copydoc Plop_HandlerInterface::handle().
    public function handle(Plop_RecordInterface $record)
    {
        $rv = $this->_format($record);
        if ($rv) {
            $this->_emit($record);
        }
        return $this;
    }

    /// \copydoc Plop_HandlerInterface::handleError().
    public function handleError(
        Plop_RecordInterface    $record,
        Exception               $exception
    )
    {
        $stderr = fopen('php://stderr', 'at');
        fprintf($stderr, "%s\n", $exception);
        fclose($stderr);
        return $this;
    }
}

