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

    /// A list with the handlers currently associated with this logger.
    protected $_handlers;

    /// Whether a warning has been emitted about this logger having no handlers.
    protected $_emittedWarning;

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
     */
    public function __construct($file = NULL, $class = NULL, $method = NULL)
    {
        parent::__construct();
        $this->_file            = $file;
        $this->_class           = $class;
        $this->_method          = $method;
        $this->_level           = Plop::NOTSET;
        $this->_handlers        = array();
        $this->_emittedWarning  = FALSE;
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
            $record = new Plop_Record(
                $this->_method . ":" . $this->_class . ":" . $this->_file,
                $level,
                $caller['fn'] ? $caller['fn'] : '???',
                $caller['lno'],
                $msg,
                $args,
                $exception,
                $caller['func'] ? $caller['func'] : NULL
            );
            $this->handle($record);
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
    protected function handle(Plop_RecordInterface $record)
    {
        if ($this->filter($record)) {
            $this->_callHandlers($record);
        }
        return $this;
    }

    /// \copydoc Plop_LoggerInterface::addHandler().
    public function addHandler(Plop_HandlerInterface $handler)
    {
        if (!in_array($handler, $this->_handlers, TRUE))
            $this->_handlers[] = $handler;
        return $this;
    }

    /// \copydoc Plop_LoggerInterface::removeHandler().
    public function removeHandler(Plop_HandlerInterface $handler)
    {
        $keys = array_keys($this->_handlers, $handler);
        if ($keys[0] !== FALSE) {
            unset($this->_filters[$keys[0]]);
        }
        return $this;
    }

    /// \copydoc Plop_LoggerInterface::getHandlers().
    public function getHandlers()
    {
        return $this->_handlers;
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
        $found = 0;
        foreach ($this->_handlers as $handler) {
            $found += 1;
            if ($record['levelno'] >= $handler->getLevel()) {
                $handler->handle($record);
            }
        }

        if (!$found && !$this->_emittedWarning) {
            $stderr = fopen('php://stderr', 'at');
            fprintf(
                $stderr,
                'No handlers could be found for logger "%s"'."\n",
                $this->_name
            );
            fclose($stderr);
            $this->_emittedWarning = TRUE;
        }
        return $this;
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
            throw new Plop_Exception('Not a valid integer');
        }
        $this->_level = $level;
        return $this;
    }

    /// \copydoc Plop_LoggerInterface::isEnabledFor().
    public function isEnabledFor($level)
    {
        return ($level >= $this->_level);
    }
}

