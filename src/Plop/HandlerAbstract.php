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
implements      Plop_HandlerInterface
{
    /// Formatter object to use for this handler.
    protected $_formatter;

    /// An object handling a collection of filters.
    protected $_filters;

    /**
     * Create a new handler that accepts any log record.
     *
     * \param Plop_FormatterInterface $formatter
     *      (optional) The formatter this handler will use
     *      to render records. By default, a new instance
     *      of Plop_Formatter is created.
     *
     * \param Plop_FiltersCollectionInterface $filters
     *      (optional) A collection of filters to associate
     *      with this handler. Defaults to an empty list.
     */
    public function __construct(
        Plop_FormatterInterface         $formatter  = NULL,
        Plop_FiltersCollectionInterface $filters    = NULL
    )
    {
        if ($formatter === NULL) {
            $formatter = new Plop_Formatter();
        }
        if ($filters === NULL) {
            $filters = new Plop_FiltersCollection();
        }
        $this->setFormatter($formatter);
        $this->_filters = $filters;
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

    /// \copydoc Plop_HandlerInterface::getFilters().
    public function getFilters()
    {
        return $this->_filters;
    }

    /// \copydoc Plop_HandlerInterface::setFilters().
    public function setFilters(Plop_FiltersCollectionInterface $filters)
    {
        $this->_filters = $filters;
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

    /**
     * Return \a STDERR as a (closable) stream.
     * This method only exists to provide an easy way
     * to mock \a STDERR in unit tests.
     *
     * \retval resource
     *      \a STDERR as a closable stream.
     *
     * @codeCoverageIgnore
     */
    protected function _getStderr()
    {
        return fopen('php://stderr', 'at');
    }

    /// \copydoc Plop_HandlerInterface::handleError().
    public function handleError(
        Plop_RecordInterface    $record,
        Exception               $exception
    )
    {
        $stderr = $this->_getStderr();
        fprintf($stderr, "%s\n", $exception);
        fclose($stderr);
        return $this;
    }
}

