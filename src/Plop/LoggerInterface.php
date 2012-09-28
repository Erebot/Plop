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

interface   Plop_LoggerInterface
extends     Plop_FiltererInterface
{
    public function getEffectiveLevel();
    public function setLevel($level);
    public function isEnabledFor($level);

    public function getFile();
    public function getClass();
    public function getMethod();
    public function getId();

    public function debug($msg, $args = array(), $exception = NULL);
    public function info($msg, $args = array(), $exception = NULL);
    public function warning($msg, $args = array(), $exception = NULL);
    public function warn($msg, $args = array(), $exception = NULL);
    public function error($msg, $args = array(), $exception = NULL);
    public function critical($msg, $args = array(), $exception = NULL);
    public function fatal($msg, $args = array(), $exception = NULL);
    public function exception($msg, $exception, $args = array());
    public function log($level, $msg, $args = array(), $exception = NULL);

    public function addHandler(Plop_HandlerInterface $handler);
    public function removeHandler(Plop_HandlerInterface $handler);
}

