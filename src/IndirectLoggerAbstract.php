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
 *      An abstract class that can be used to proxy
 *      logging calls to an actual logger.
 *
 * Subclasses should implement the _getIndirectLogger() method.
 * You may then use methods from the Plop::LoggerInterface
 * interface on instances of this class' subclasses.
 * Such calls will be proxied to the actual logger returned by
 * the _getIndirectLogger() method.
 */
abstract class IndirectLoggerAbstract extends LoggerAbstract
{
    /// \copydoc Plop::LoggerInterface::log().
    public function log(
        $level,
        $msg,
        array $args = array(),
        \Exception $exception = null
    ) {
        $logger = $this->getIndirectLogger();
        $logger->log($level, $msg, $args, $exception);
        return $this;
    }

    /// \copydoc Plop::LoggerInterface::getLevel().
    public function getLevel()
    {
        return $this->getIndirectLogger()->getLevel();
    }

    /// \copydoc Plop::LoggerInterface::setLevel().
    public function setLevel($level)
    {
        $logger = $this->getIndirectLogger();
        $logger->setLevel($level);
        return $this;
    }

    /// \copydoc Plop::LoggerInterface::isEnabledFor().
    public function isEnabledFor($level)
    {
        return $this->getIndirectLogger()->isEnabledFor($level);
    }

    /// \copydoc Plop::LoggerInterface::getNamespace().
    public function getNamespace()
    {
        return $this->getIndirectLogger()->getNamespace();
    }

    /// \copydoc Plop::LoggerInterface::getClass().
    public function getClass()
    {
        return $this->getIndirectLogger()->getClass();
    }

    /// \copydoc Plop::LoggerInterface::getMethod().
    public function getMethod()
    {
        return $this->getIndirectLogger()->getMethod();
    }

    /// \copydoc Plop::LoggerInterface::getRecordFactory().
    public function getRecordFactory()
    {
        return $this->getIndirectLogger()->getRecordFactory();
    }

    /// \copydoc Plop::LoggerInterface::setRecordFactory(Plop::RecordFactoryInterface $factory).
    public function setRecordFactory(\Plop\RecordFactoryInterface $factory)
    {
        $logger = $this->getIndirectLogger();
        $logger->setRecordFactory($factory);
        return $this;
    }

    /// \copydoc Plop::LoggerInterface::getHandlers().
    public function getHandlers()
    {
        return $this->getIndirectLogger()->getHandlers();
    }

    /// \copydoc Plop::LoggerInterface::setHandlers().
    public function setHandlers(\Plop\HandlersCollectionAbstract $handlers)
    {
        $logger = $this->getIndirectLogger();
        $logger->setHandlers($handlers);
        return $this;
    }

    /// \copydoc Plop::LoggerInterface::getFilters().
    public function getFilters()
    {
        return $this->getIndirectLogger()->getFilters();
    }

    /// \copydoc Plop::HandlerInterface::setFilters().
    public function setFilters(\Plop\FiltersCollectionAbstract $filters)
    {
        $logger = $this->getIndirectLogger();
        $logger->setFilters($filters);
        return $this;
    }

    /**
     * Return the actual logger that will be used to proxy
     * calls to methods of the Plop::LoggerInterface interface.
     *
     * \retval Plop::LoggerInterface
     *      Actual logger to proxy calls to.
     */
    abstract protected function getIndirectLogger();
}
