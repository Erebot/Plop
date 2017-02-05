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

namespace Plop;

/**
 *  \brief
 *      An abstract class that can be used as a base
 *      to create new log records handlers.
 *
 *  Subclasses must implement the _emit() method.
 */
abstract class HandlerAbstract implements \Plop\HandlerInterface
{
    /// Formatter object to use for this handler.
    protected $formatter;

    /// An object handling a collection of filters.
    protected $filters;

    /**
     * Create a new handler that accepts any log record.
     *
     * \param Plop::FormatterInterface $formatter
     *      (optional) The formatter this handler will use
     *      to render records. By default, a new instance
     *      of Plop::Formatter is created.
     *
     * \param Plop::FiltersCollectionAbstract $filters
     *      (optional) A collection of filters to associate
     *      with this handler. Defaults to an empty list.
     */
    public function __construct(
        \Plop\FormatterInterface $formatter = null,
        \Plop\FiltersCollectionAbstract $filters = null
    ) {
        if ($formatter === null) {
            $formatter = new \Plop\Formatter();
        }
        if ($filters === null) {
            $filters = new \Plop\FiltersCollection();
        }
        $this->setFormatter($formatter);
        $this->filters = $filters;
    }

    /// \copydoc Plop::HandlerInterface::getFormatter
    public function getFormatter()
    {
        return $this->formatter;
    }

    /// \copydoc Plop::HandlerInterface::setFormatter
    public function setFormatter(\Plop\FormatterInterface $formatter)
    {
        $this->formatter = $formatter;
        return $this;
    }

    /// \copydoc Plop::HandlerInterface::getFilters
    public function getFilters()
    {
        return $this->filters;
    }

    /// \copydoc Plop::HandlerInterface::setFilters
    public function setFilters(\Plop\FiltersCollectionAbstract $filters)
    {
        $this->filters = $filters;
        return $this;
    }

    /**
     * Format a record.
     *
     * \param Plop::RecordInterface $record
     *      The record to format.
     *
     * \retval string
     *      Formatted representation of the record.
     */
    protected function format(\Plop\RecordInterface $record)
    {
        return $this->formatter->format($record);
    }

    /**
     * Send the log to its final destination.
     *
     * \param Plop::RecordInterface $record
     *      The record to log.
     *
     * \return
     *      This method does not return any value.
     */
    abstract protected function emit(\Plop\RecordInterface $record);

    /// \copydoc Plop::HandlerInterface::handle
    public function handle(\Plop\RecordInterface $record)
    {
        $rv = $this->format($record);
        if ($rv) {
            $this->emit($record);
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
    protected function getStderr()
    {
        return fopen('php://stderr', 'at');
    }

    /// \copydoc Plop::HandlerInterface::handleError
    public function handleError(
        \Plop\RecordInterface $record,
        \Exception $exception
    ) {
        $stderr = $this->getStderr();
        fprintf($stderr, "%s\n", $exception);
        fclose($stderr);
        return $this;
    }
}
