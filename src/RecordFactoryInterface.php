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
 *      An interface for a factory that produces
 *      instances that implement the Plop::RecordInterface
 *      interface.
 */
interface RecordFactoryInterface
{
    /**
     * Create a new log record.
     *
     * \param string $loggerNamespace
     *      Namespace associated with the logger that captured the log.
     *
     * \param string $loggerClass
     *      Class associated with the logger that captured the log.
     *
     * \param string $loggerMethod
     *      Method of function associated with the logger that captured the log.
     *
     * \param string $namespace
     *      Namespace where the log was emitted.
     *
     * \param string $class
     *      Class where the log was emitted.
     *
     * \param string $method
     *      Method or function where the log was emitted.
     *
     * \param int $level
     *      Level of the log record.
     *
     * \param string $filename
     *      Full path to the file where the log
     *      was emitted.
     *
     * \param int $lineno
     *      Line number where the log was emitted.
     *
     * \param string $msg
     *      Log message.
     *
     * \param array $args
     *      Associative array of additional information
     *      to keep in the log record.
     *
     * \param Exception|null $exception
     *      Either an exception that will be logged
     *      together with the rest of the record,
     *      or \a null in case there is no exception
     *      to log.
     *
     * \retval Plop::RecordInterface
     *      The newly-created log record.
     */
    public function createRecord(
        $loggerNamespace,
        $loggerClass,
        $loggerMethod,
        $namespace,
        $class,
        $method,
        $level,
        $filename,
        $lineno,
        $msg,
        array $args,
        \Exception $exception = null
    );
}
