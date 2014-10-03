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
 *      A factory that creates log records
 *      as new instances of the Plop::Record class.
 */
class RecordFactory implements \Plop\RecordFactoryInterface
{
    protected $interpolator;

    public function __construct(\Plop\InterpolatorInterface $interpolator = null) {
        if ($interpolator === null) {
            $interpolator = new \Plop\Interpolator\Percent();
        }
        $this->interpolator = $interpolator;
    }

    /// \copydoc Plop::RecordFactoryInterface::createRecord().
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
    ) {
        $record = new \Plop\Record(
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
            $args,
            $this->interpolator,
            $exception
        );
        return $record;
    }
}
