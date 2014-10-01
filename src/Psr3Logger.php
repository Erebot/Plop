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
 *      A special logger which acts as a bridge between
 *      PSR-3 loggers and Plop.
 */
class Psr3Logger extends \Psr\Log\AbstractLogger
{
    protected $factory = null;

    public function log($level, $message, array $context = array())
    {
        /***
         * \note
         *      There are several contexts in which the follow code
         *      may not work as expected.
         *
         *      Using threads (https://github.com/krakjoe/pthreads)
         *      with this method will probably mess things up.
         *
         *      The following functions may also cause trouble
         *      depending on how they're used:
         *      - register_tick_function()
         *      - debug_backtrace()
         */

        if ($this->factory === null) {
            $this->factory = new \Plop\RecordFactory(new \Plop\Interpolator\Psr3());
        }

        $logging = \Plop\Plop::getInstance();
        $factory = $logging->getRecordFactory();
        try {
            // Switch to a factory that uses PSR3-interpolation.
            $logging->setRecordFactory($this->factory);
            $logging->$level($message, $context);
        } catch (Exception $e) {}

        // Restore the original factory.
        $logging->setRecordFactory($factory);
    }
}
