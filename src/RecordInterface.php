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

namespace Plop;

/**
 *  \brief
 *      Interface for a log record.
 *
 * The log record may be used like an (associative)
 * array to add/remove/change its contents. You may
 * also call the asArray() method to get a true array.
 */
interface RecordInterface extends \ArrayAccess, \Serializable
{
    /**
     * Return this record's message interpolator.
     *
     * \retval Plop::InterpolatorInterface
     *      Message interpolator.
     */
    public function getInterpolator();

    /**
     * Set the message interpolator for this record.
     *
     * \param Plop::InterpolatorInterface $interpolator
     *      Message interpolator to use.
     *
     * \retval Plop::RecordInterface
     *      The record (ie. \a $this).
     */
    public function setInterpolator(\Plop\InterpolatorInterface $interpolator);

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
     * The properties of this log record,
     * as an associative array.
     *
     * \retval array
     *      An associative array with the properties
     *      of this log record.
     */
    public function asArray();
}
