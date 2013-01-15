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
 *      Interface to a record formatter.
 */
interface Plop_FormatterInterface
{
    /**
     * Return the general format used to render
     * records.
     *
     * \retval string
     *      The format used to render records.
     */
    public function getFormat();

    /**
     * Set the format used to render records.
     *
     * \param string $format
     *      The new format to use.
     *
     * \retval Plop_FormatterInterface
     *      The formatter instance (ie. \a $this).
     */
    public function setFormat($format);

    /**
     * Return the format used to render dates.
     *
     * \retval string
     *      The format used to render dates.
     */
    public function getDateFormat();

    /**
     * Set the format used to render dates.
     *
     * \param string $dateFormat
     *      The new format to use.
     *
     * \retval Plop_FormatterInterface
     *      The formatter instance (ie. \a $this).
     */
    public function setDateFormat($dateFormat);

    /**
     * Return the timezone object used to format
     * dates/times.
     *
     * \retval DateTimeZone
     *      Timezone object used to format dates/times.
     *
     * \retval NULL
     *      Returned when no particular timezone is used
     *      to format dates/times (ie. the local timezone
     *      is used).
     */
    public function getTimezone();

    /**
     * Set the timezone to use to format dates/times.
     *
     * \param DateTimeZone|NULL $timezone
     *      (optional) Timezone to use to format dates/times.
     *      If \a NULL or omitted, the defaut (local) timezone
     *      is used.
     *
     * \retval Plop_FormatterInterface
     *      The formatter instance (ie. \a $this).
     */
    public function setTimezone(DateTimeZone $timezone = NULL);

    /**
     * Format a record.
     *
     * \param Plop_RecordInterface $record
     *      The record to format.
     *
     * \retval string
     *      Formatted string representing the given record.
     */
    public function format(Plop_RecordInterface $record);
}

