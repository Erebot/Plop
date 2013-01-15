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
 *      A class that provides logging capabilities.
 */
class       Plop_Logger
extends     Plop_LoggerAbstract
{
    /// Name of the file this logger relates to.
    protected $_file;

    /// Name of the class this logger relates to.
    protected $_class;

    /// Name of the method or function this logger relates to.
    protected $_method;

    /// Minimum level at which this logger will accept log entries.
    protected $_level;

    /// A collection of handlers currently associated with this logger.
    protected $_handlers;

    /// Whether a warning has been emitted about this logger having no handlers.
    protected $_emittedWarning;

    /// An object handling a collection of filters.
    protected $_filters;

    /**
     * Construct a new logger with no attached handlers
     * and that accepts any log entry.
     *
     * \param string|NULL $file
     *      (optional) The name of the file this logger
     *      relates to. The default is \a NULL
     *      (meaning this logger is not related to any
     *      specific file).
     *
     * \param string|NULL $class
     *      (optional) The name of the class this logger
     *      relates to. The default is \a NULL
     *      (meaning this logger is not related to any
     *      specific class).
     *
     * \param string|NULL $method
     *      (optional) The name of the method or function
     *      this logger relates to. The default is \a NULL
     *      (meaning this logger is not related to any
     *      specific method or function).
     *
     * \param Plop_HandlersCollectionInterface $handlers
     *      (optional) A collection of handlers to associate
     *      with this logger. Defaults to an empty list.
     *
     * \param Plop_FiltersCollectionInterface $filters
     *      (optional) A collection of filters to associate
     *      with this logger. Defaults to an empty list.
     */
    public function __construct(
                                            $file       = NULL,
                                            $class      = NULL,
                                            $method     = NULL,
        Plop_HandlersCollectionInterface    $handlers   = NULL,
        Plop_FiltersCollectionInterface     $filters    = NULL
    )
    {
        if ($handlers === NULL) {
            $handlers = new Plop_HandlersCollection();
        }
        if ($filters === NULL) {
            $filters = new Plop_FiltersCollection();
        }
        while (substr($file, -strlen(DIRECTORY_SEPARATOR)) ==
            DIRECTORY_SEPARATOR) {
            $file = (string) substr($file, 0, -strlen(DIRECTORY_SEPARATOR));
        }
        $this->_file            = $file;
        $this->_class           = $class;
        $this->_method          = $method;
        $this->_level           = Plop::NOTSET;
        $this->_handlers        = $handlers;
        $this->_emittedWarning  = FALSE;
        $this->_filters         = $filters;
        $this->setRecordFactory(new Plop_RecordFactory());
    }

    /// \copydoc Plop_LoggerInterface::getFile().
    public function getFile()
    {
        return $this->_file;
    }

    /// \copydoc Plop_LoggerInterface::getClass().
    public function getClass()
    {
        return $this->_class;
    }

    /// \copydoc Plop_LoggerInterface::getMethod().
    public function getMethod()
    {
        return $this->_method;
    }

    /// \copydoc Plop_LoggerInterface::getRecordFactory().
    public function getRecordFactory()
    {
        return $this->_recordFactory;
    }

    /// \copydoc Plop_LoggerInterface::setRecordFactory().
    public function setRecordFactory(Plop_RecordFactoryInterface $factory)
    {
        $this->_recordFactory = $factory;
        return $this;
    }

    /// \copydoc Plop_LoggerInterface::getFilters().
    public function getFilters()
    {
        return $this->_filters;
    }

    /// \copydoc Plop_LoggerInterface::setFilters().
    public function setFilters(Plop_FiltersCollectionInterface $filters)
    {
        $this->_filters = $filters;
        return $this;
    }

    /// \copydoc Plop_LoggerInterface::getHandlers().
    public function getHandlers()
    {
        return $this->_handlers;
    }

    /// \copydoc Plop_LoggerInterface::setHandlers().
    public function setHandlers(Plop_HandlersCollectionInterface $handlers)
    {
        $this->_handlers = $handlers;
        return $this;
    }

    /// \copydoc Plop_LoggerInterface::log().
    public function log(
                    $level,
                    $msg,
        array       $args       = array(),
        Exception   $exception  = NULL
    )
    {
        if ($this->isEnabledFor($level)) {
            $caller = Plop::findCaller();
            $record = $this->_recordFactory->createRecord(
                $this->_file,
                $this->_class,
                $this->_method,
                $level,
                $caller['fn'] ? $caller['fn'] : '???',
                $caller['lno'],
                $msg,
                $args,
                $caller['func'] ? $caller['func'] : NULL,
                $exception
            );
            $this->_handle($record);
        }
        return $this;
    }

    /**
     * Handle a log record.
     *
     * \param Plop_RecordInterface $record
     *      The log record to handle.
     *
     * \retval Plop_LoggerInterface
     *      The logger instance (ie. \a $this).
     */
    protected function _handle(Plop_RecordInterface $record)
    {
        if ($this->_filters->filter($record)) {
            $this->_callHandlers($record);
        }
        return $this;
    }

    /**
     * Call every handler associated with this logger
     * in turn, passing them a log record to handle.
     *
     * \param Plop_RecordInterface $record
     *      The log record to handle.
     *
     * \retval Plop_LoggerInterface
     *      The logger instance (ie. \a $this).
     */
    protected function _callHandlers(Plop_RecordInterface $record)
    {
        if (!count($this->_handlers) && !$this->_emittedWarning) {
            $stderr = $this->_getStderr();
            fprintf(
                $stderr,
                'No handlers could be found for logger ("%s" in "%s")' . "\n",
                $this->_class .
                ($this->_class === NULL || $this->_class === '' ? '' : '::') .
                $this->_method,
                $this->_file
            );
            fclose($stderr);
            $this->_emittedWarning = TRUE;
            return $this;
        }

        $this->_handlers->handle($record);
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
    protected function _getStderr()
    {
        return fopen('php://stderr', 'at');
    }

    /// \copydoc Plop_LoggerInterface::getLevel().
    public function getLevel()
    {
        return $this->_level;
    }

    /// \copydoc Plop_LoggerInterface::setLevel().
    public function setLevel($level)
    {
        if (!is_int($level)) {
            throw new Plop_Exception('Invalid value');
        }
        $this->_level = $level;
        return $this;
    }

    /// \copydoc Plop_LoggerInterface::isEnabledFor().
    public function isEnabledFor($level)
    {
        if (!is_int($level)) {
            throw new Plop_Exception('Invalid value');
        }
        return ($level >= $this->_level);
    }
}

