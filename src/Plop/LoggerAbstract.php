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

abstract class  Plop_LoggerAbstract
extends         Plop_Filterer
implements      Plop_LoggerInterface
{
    public function debug($msg, $args = array(), $exception = NULL)
    {
        return $this->log(Plop::DEBUG, $msg, $args, $exception);
    }

    public function info($msg, $args = array(), $exception = NULL)
    {
        return $this->log(Plop::INFO, $msg, $args, $exception);
    }

    public function warning($msg, $args = array(), $exception = NULL)
    {
        return $this->log(Plop::WARNING, $msg, $args, $exception);
    }

    public function warn($msg, $args = array(), $exception = NULL)
    {
        return $this->log(Plop::WARN, $msg, $args, $exception);
    }

    public function error($msg, $args = array(), $exception = NULL)
    {
        return $this->log(Plop::ERROR, $msg, $args, $exception);
    }

    public function critical($msg, $args = array(), $exception = NULL)
    {
        return $this->log(Plop::CRITICAL, $msg, $args, $exception);
    }

    public function fatal($msg, $args = array(), $exception = NULL)
    {
        return $this->log(Plop::CRITICAL, $msg, $args, $exception);
    }

    public function exception($msg, $exception, $args = array())
    {
        return $this->log(Plop::ERROR, $msg, $args, $exception);
    }
}

