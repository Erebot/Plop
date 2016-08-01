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
 *      A class that provides logging capabilities.
 */
class Logger extends \Plop\LoggerAbstract
{
    /// Name of the namespace this logger relates to.
    protected $ns;

    /// Name of the class this logger relates to.
    protected $cls;

    /// Name of the method or function this logger relates to.
    protected $method;

    /// Minimum level at which this logger will accept log entries.
    protected $level;

    /// A collection of handlers currently associated with this logger.
    protected $handlers;

    /// Whether a warning has been emitted about this logger having no handlers.
    protected $emittedWarning;

    /// An object handling a collection of filters.
    protected $filters;

    /**
     * Construct a new logger with no attached handlers
     * and that accepts any log entry.
     *
     * \param string|null $ns
     *      (optional) The name of the namespace this logger relates to.
     *      The default is \a null (meaning this logger is related
     *      to the global namespace).
     *
     * \param string|null $cls
     *      (optional) The name of the class this logger
     *      relates to. The default is \a null
     *      (meaning this logger is not related to any
     *      specific class).
     *
     * \param string|null $method
     *      (optional) The name of the method or function
     *      this logger relates to. The default is \a null
     *      (meaning this logger is not related to any
     *      specific method or function).
     *
     * \param Plop::HandlersCollectionAbstract $handlers
     *      (optional) A collection of handlers to associate
     *      with this logger. Defaults to an empty list.
     *
     * \param Plop::FiltersCollectionAbstract $filters
     *      (optional) A collection of filters to associate
     *      with this logger. Defaults to an empty list.
     */
    public function __construct(
        $ns = null,
        $cls = null,
        $method = null,
        \Plop\HandlersCollectionAbstract $handlers = null,
        \Plop\FiltersCollectionAbstract $filters = null
    ) {
        if ($handlers === null) {
            $handlers = new \Plop\HandlersCollection();
        }
        if ($filters === null) {
            $filters = new \Plop\FiltersCollection();
        }
        if ($ns !== null) {
            while (substr($ns, -strlen('\\')) == '\\') {
                $ns = (string) substr($ns, 0, -strlen('\\'));
            }
        }

        // Strip potential namespace from class name.
        $cls = (string) substr($cls, strrpos('\\' . $cls, '\\'));
        if ($cls === '') {
            $cls = null;
        }

        // Strip potential namespace from method/function name.
        $method = (string) substr($method, strrpos('\\' . $method, '\\'));
        if ($method === '') {
            $method = null;
        }

        $this->ns               = $ns;
        $this->cls              = $cls;
        $this->method           = $method;
        $this->level            = \Plop\NOTSET;
        $this->handlers         = $handlers;
        $this->emittedWarning   = false;
        $this->filters          = $filters;
        $this->setRecordFactory(new \Plop\RecordFactory());
    }

    /// \copydoc Plop::LoggerInterface::getNamespace().
    public function getNamespace()
    {
        return $this->ns;
    }

    /// \copydoc Plop::LoggerInterface::getClass().
    public function getClass()
    {
        return $this->cls;
    }

    /// \copydoc Plop::LoggerInterface::getMethod().
    public function getMethod()
    {
        return $this->method;
    }

    /// \copydoc Plop::LoggerInterface::getRecordFactory().
    public function getRecordFactory()
    {
        return $this->recordFactory;
    }

    /// \copydoc Plop::LoggerInterface::setRecordFactory().
    public function setRecordFactory(\Plop\RecordFactoryInterface $factory)
    {
        $this->recordFactory = $factory;
        return $this;
    }

    /// \copydoc Plop::LoggerInterface::getFilters().
    public function getFilters()
    {
        return $this->filters;
    }

    /// \copydoc Plop::LoggerInterface::setFilters().
    public function setFilters(\Plop\FiltersCollectionAbstract $filters)
    {
        $this->filters = $filters;
        return $this;
    }

    /// \copydoc Plop::LoggerInterface::getHandlers().
    public function getHandlers()
    {
        return $this->handlers;
    }

    /// \copydoc Plop::LoggerInterface::setHandlers().
    public function setHandlers(\Plop\HandlersCollectionAbstract $handlers)
    {
        $this->handlers = $handlers;
        return $this;
    }

    /// \copydoc Plop::LoggerInterface::log().
    public function log(
        $level,
        $msg,
        array $args = array(),
        \Exception $exception = null
    ) {
        if ($this->isEnabledFor($level)) {
            $caller = \Plop\Plop::findCaller();
            $record = $this->recordFactory->createRecord(
                $this->ns,
                $this->cls,
                $this->method,
                $caller['ns'],
                $caller['cls'],
                $caller['func'],
                $level,
                $caller['file'] ? $caller['file'] : '???',
                $caller['line'],
                $msg,
                $args,
                $exception
            );
            $this->handle($record);
        }
        return $this;
    }

    /**
     * Handle a log record.
     *
     * \param Plop::RecordInterface $record
     *      The log record to handle.
     *
     * \retval Plop::LoggerInterface
     *      The logger instance (ie. \a $this).
     */
    protected function handle(\Plop\RecordInterface $record)
    {
        if ($this->filters->filter($record)) {
            $this->callHandlers($record);
        }
        return $this;
    }

    /**
     * Call every handler associated with this logger
     * in turn, passing them a log record to handle.
     *
     * \param Plop::RecordInterface $record
     *      The log record to handle.
     *
     * \retval Plop::LoggerInterface
     *      The logger instance (ie. \a $this).
     */
    protected function callHandlers(\Plop\RecordInterface $record)
    {
        if (!count($this->handlers) && !$this->emittedWarning) {
            $stderr = $this->getStderr();
            fprintf(
                $stderr,
                'No handlers could be found for logger ("%s" in "%s")' . "\n",
                $this->cls .
                ($this->cls === null || $this->cls === '' ? '' : '::') .
                $this->method,
                $this->ns
            );
            fclose($stderr);
            $this->emittedWarning = true;
            return $this;
        }

        $this->handlers->handle($record);
        return $this;
    }

    /**
     * Return \a STDERR as a (closable) stream.
     * This method only exists to provide an easy way
     * to mock \a STDERR in unit tests.
     *
     * \retval resource
     *      \a STDERR as a closable stream.
     *
     * @codeCoverageIgnore
     */
    protected function getStderr()
    {
        return fopen('php://stderr', 'at');
    }

    /// \copydoc Plop::LoggerInterface::getLevel().
    public function getLevel()
    {
        return $this->level;
    }

    /// \copydoc Plop::LoggerInterface::setLevel().
    public function setLevel($level)
    {
        if (!is_int($level)) {
            $plop = \Plop\Plop::getInstance();
            $level = $plop->getLevelValue($level);
        }
        $this->level = $level;
        return $this;
    }

    /// \copydoc Plop::LoggerInterface::isEnabledFor().
    public function isEnabledFor($level)
    {
        if (!is_int($level)) {
            $plop = \Plop\Plop::getInstance();
            $level = $plop->getLevelValue($level);
        }
        return ($level >= $this->level);
    }
}
