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
 *      An abstract class that can be used to proxy
 *      logging calls to an actual logger.
 *
 * Subclasses should implement the _getIndirectLogger() method.
 * You may then use methods from the Plop_LoggerInterface
 * interface on instances of this class' subclasses.
 * Such calls will be proxied to the actual logger returned by
 * the _getIndirectLogger() method.
 */
abstract class  Plop_IndirectLoggerAbstract
extends         Plop_LoggerAbstract
{
    /// \copydoc Plop_LoggerInterface::log().
    public function log(
                    $level, 
                    $msg,
        array       $args       = array(),
        Exception   $exception  = NULL
    )
    {
        $logger = $this->_getIndirectLogger();
        $logger->log($level, $msg, $args, $exception);
    }

    /// \copydoc Plop_LoggerInterface::getLevel().
    public function getLevel()
    {
        $logger = $this->_getIndirectLogger();
        return $logger->getLevel();
    }

    /// \copydoc Plop_LoggerInterface::setLevel().
    public function setLevel($level)
    {
        $logger = $this->_getIndirectLogger();
        $logger->setLevel($level);
        return $this;
    }

    /// \copydoc Plop_LoggerInterface::isEnabledFor().
    public function isEnabledFor($level)
    {
        $logger = $this->_getIndirectLogger();
        return $logger->isEnabledFor($level);
    }

    /// \copydoc Plop_LoggerInterface::getFile().
    public function getFile()
    {
        $logger = $this->_getIndirectLogger();
        return $logger->getFile();
    }

    /// \copydoc Plop_LoggerInterface::getClass().
    public function getClass()
    {
        $logger = $this->_getIndirectLogger();
        return $logger->getClass();
    }

    /// \copydoc Plop_LoggerInterface::getMethod().
    public function getMethod()
    {
        $logger = $this->_getIndirectLogger();
        return $logger->getMethod();
    }

    /// \copydoc Plop_LoggerInterface::addHandler().
    public function addHandler(Plop_HandlerInterface $handler)
    {
        $logger = $this->_getIndirectLogger();
        $logger->addHandler($handler);
        return $this;
    }

    /// \copydoc Plop_LoggerInterface::removeHandler().
    public function removeHandler(Plop_HandlerInterface $handler)
    {
        $logger = $this->_getIndirectLogger();
        $logger->removeHandler($handler);
        return $this;
    }

    /// \copydoc Plop_LoggerInterface::getHandlers().
    public function getHandlers()
    {
        $logger = $this->_getIndirectLogger();
        return $logger->getHandlers();
    }

    /// \copydoc Plop_FiltererInterface::addFilter().
    public function addFilter(Plop_FilterInterface $filter)
    {
        $logger = $this->_getIndirectLogger();
        $logger->addFilter($filter);
        return $this;
    }

    /// \copydoc Plop_FiltererInterface::removeFilter().
    public function removeFilter(Plop_FilterInterface $filter)
    {
        $logger = $this->_getIndirectLogger();
        $logger->removeFilter($filter);
        return $this;
    }

    /// \copydoc Plop_FiltererInterface::getFilters().
    public function getFilters()
    {
        $logger = $this->_getIndirectLogger();
        return $logger->getFilters();
    }

    /// \copydoc Plop_FiltererInterface::filter().
    public function filter(Plop_RecordInterface $record)
    {
        $logger = $this->_getIndirectLogger();
        return $logger->filter($record);
    }

    /**
     * Return the actual logger that will be used to proxy
     * calls to methods of the Plop_LoggerInterface interface.
     *
     * \retval Plop_LoggerInterface
     *      Actual logger to proxy calls to.
     */
    abstract protected function _getIndirectLogger();
}

