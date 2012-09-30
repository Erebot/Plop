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
 *      Interface for a class capable of handling log records.
 */
interface Plop_HandlerInterface
{
    /**
     * Return the minimum level a log record must have
     * to be handled by this class.
     *
     * \retval int
     *      Minimum level at which log records
     *      will be accepted.
     */
    public function getLevel();

    /**
     * Set the minimum level a log record must have
     * to be handled by this class.
     *
     * \param int $level
     *      The new minimum level at which log records
     *      will be accepted.
     *
     * \retval Plop_HandlerInterface
     *      The handler instance (ie. \a $this).
     */
    public function setLevel($level);

    /**
     * Return the formatter used by this handler.
     *
     * \retval Plop_FormatterInterface
     *      Formatter object used by this handler.
     */
    public function getFormatter();

    /**
     * Set the new formatter to use.
     *
     * \param Plop_FormatterInterface $formatter
     *      The new formatter object to use to format
     *      log records.
     *
     * \retval Plop_HandlerInterface
     *      The handler instance (ie. \a $this).
     */
    public function setFormatter(Plop_FormatterInterface $formatter);

    /**
     * Handle a log record.
     *
     * \param Plop_RecordInterface $record
     *      The log record to handle.
     *
     * \retval Plop_HandlerInterface
     *      The handler instance (ie. \a $this).
     */
    public function handle(Plop_RecordInterface $record);

    /**
     * Handle an exception that occurred during
     * log record handling.
     *
     * \param Plop_RecordInterface $record
     *      The log record that was being handled
     *      when the exception occurred.
     *
     * \param Exception $exception
     *      The actual exception.
     *
     * \retval Plop_HandlerInterface
     *      The handler instance (ie. \a $this).
     */
    public function handleError(
        Plop_RecordInterface $record,
        Exception            $exception
    );
}

