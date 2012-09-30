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
 *      Interface for a log record.
 *
 * The log record may be used like an (associative)
 * array to add/remove/change its contents. You may
 * also call the asArray() method to get a true array.
 */
interface   Plop_RecordInterface
extends     ArrayAccess,
            Serializable
{
    /**
     * Return the message stored in this log record,
     * after all interpolated variables have been
     * replaced in it.
     *
     * \retval string
     *      The message stored in the log record.
     */
    public function getMessage();

    /**
     * Interpolate percent-sequence-variables in
     * a message.
     *
     * \param string $msg
     *      The message that serves as the model
     *      for the result. It may contain refer to
     *      the values of variables dynamically using
     *      special percent-sequences (read the rest
     *      of this entry for more information).
     *
     * \param array $args
     *      Associative array of values that may
     *      be replaced dynamically in the message,
     *      using a formatting sequence based on this model:
     *      \verbatim %(name)<spec> \endverbatim
     *      where \a name is the key of the variable, taken from
     *      the \a $args parameter, and \a \<spec\> is a valid
     *      <a href="http://php.net/sprintf">sprintf()</a> format
     *      specification, such as \verbatim %(foo)04d \endverbatim.
     *
     * \retval string
     *      The message with all of its percent-sequences
     *      replaced with their proper value.
     */
    static public function formatPercent($msg, array $args);

    /**
     * The properties of this log record,
     * as an associative array.
     *
     * \retval array
     *      An associative array with the properties
     *      of this log record.
     */
    public function asArray();
}

