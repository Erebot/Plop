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

abstract class  Plop_IndirectLoggerAbstract
extends         Plop_LoggerAbstract
{
    public function log($level, $msg, $args = array(), $exception = NULL)
    {
        $logger = $this->_getIndirectLogger();
        $logger->log($level, $msg, $args, $exception);
    }

    public function getEffectiveLevel()
    {
        $logger = $this->_getIndirectLogger();
        return $logger->getEffectiveLevel();
    }

    public function setLevel($level)
    {
        $logger = $this->_getIndirectLogger();
        $logger->setLevel($level);
        return $this;
    }

    public function isEnabledFor($level)
    {
        $logger = $this->_getIndirectLogger();
        return $logger->isEnabledFor($level);
    }

    public function getFile()
    {
        $logger = $this->_getIndirectLogger();
        return $logger->getFile();
    }

    public function getClass()
    {
        $logger = $this->_getIndirectLogger();
        return $logger->getClass();
    }

    public function getMethod()
    {
        $logger = $this->_getIndirectLogger();
        return $logger->getMethod();
    }

    public function getId()
    {
        $logger = $this->_getIndirectLogger();
        return $logger->getId();
    }

    public function addHandler(Plop_HandlerInterface $handler)
    {
        $logger = $this->_getIndirectLogger();
        $logger->addHandler($handler);
        return $this;
    }

    public function removeHandler(Plop_HandlerInterface $handler)
    {
        $logger = $this->_getIndirectLogger();
        $logger->removeHandler($handler);
        return $this;
    }

    public function addFilter(Plop_FilterInterface $filter)
    {
        $logger = $this->_getIndirectLogger();
        $logger->addFilter($filter);
        return $this;
    }

    public function removeFilter(Plop_FilterInterface $filter)
    {
        $logger = $this->_getIndirectLogger();
        $logger->removeFilter($filter);
        return $this;
    }

    public function getFilters()
    {
        $logger = $this->_getIndirectLogger();
        return $logger->getFilters();
    }

    public function filter(Plop_RecordInterface $record)
    {
        $logger = $this->_getIndirectLogger();
        return $logger->filter($record);
    }

    abstract protected function _getIndirectLogger();
}

