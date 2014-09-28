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
 *      An abstract class that can be used as a base
 *      to create new loggers.
 */
abstract class LoggerAbstract implements \Plop\LoggerInterface
{
    /// \copydoc Plop::LoggerInterface::debug().
    public function debug(
        $msg,
        array $args = array(),
        \Exception $exception = null
    ) {
        return $this->log(\Plop\DEBUG, $msg, $args, $exception);
    }

    /// \copydoc Plop::LoggerInterface::info().
    public function info(
        $msg,
        array $args = array(),
        \Exception $exception = null
    ) {
        return $this->log(\Plop\INFO, $msg, $args, $exception);
    }

    /// \copydoc Plop::LoggerInterface::warning().
    public function warning(
        $msg,
        array $args = array(),
        \Exception $exception = null
    ) {
        return $this->log(\Plop\WARNING, $msg, $args, $exception);
    }

    /// \copydoc Plop::LoggerInterface::warn().
    public function warn(
        $msg,
        array $args = array(),
        \Exception $exception = null
    ) {
        return $this->log(\Plop\WARN, $msg, $args, $exception);
    }

    /// \copydoc Plop::LoggerInterface::error().
    public function error(
        $msg,
        array $args = array(),
        \Exception $exception = null
    ) {
        return $this->log(\Plop\ERROR, $msg, $args, $exception);
    }

    /// \copydoc Plop::LoggerInterface::critical().
    public function critical(
        $msg,
        array $args = array(),
        \Exception $exception = null
    ) {
        return $this->log(\Plop\CRITICAL, $msg, $args, $exception);
    }

    /// \copydoc Plop::LoggerInterface::fatal().
    public function fatal(
        $msg,
        array $args = array(),
        \Exception $exception = null
    ) {
        return $this->log(\Plop\CRITICAL, $msg, $args, $exception);
    }

    /// \copydoc Plop::LoggerInterface::exception().
    public function exception(
        $msg,
        \Exception $exception,
        array $args = array()
    ) {
        return $this->log(\Plop\ERROR, $msg, $args, $exception);
    }
}
