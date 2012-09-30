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
 * \brief
 *      Interface for a logger.
 */
interface   Plop_LoggerInterface
{
    /**
     * Return the minimum level a log record must have
     * to be handled by this class.
     *
     * \retval int
     *      Minimum level at which log records
     *      will be accepted.
     */
    public function getLevel();

    /**
     * Set the minimum level a log record must have
     * to be handled by this class.
     *
     * \param int $level
     *      The new minimum level at which log records
     *      will be accepted.
     *
     * \retval Plop_LoggerInterface
     *      The logger instance (ie. \a $this).
     */
    public function setLevel($level);

    /**
     * Return a flag indicating whether a log with
     * the given level would be handled by this instance.
     *
     * \param int $level
     *      Level to test.
     *
     * \retval bool
     *      \a TRUE if a log with the given level would
     *      be handled by this instance, or \a FALSE
     *      if it would be ignored.
     */
    public function isEnabledFor($level);

    /**
     * Return the name of the file this logger
     * applies to.
     *
     * \retval string
     *      Name of the file this logger relates to.
     *
     * \see
     *      Plop_LoggerInterface::getClass() and
     *      Plop_LoggerInterface::getMethod().
     */
    public function getFile();

    /**
     * Return the name of the class this logger
     * applies to.
     *
     * \retval string
     *      Name of the class this logger relates to.
     *
     * \see
     *      Plop_LoggerInterface::getFile() and
     *      Plop_LoggerInterface::getMethod().
     */
    public function getClass();

    /**
     * Return the name of the method or function
     * this logger applies to.
     *
     * \retval string
     *      Name of the method/function this logger
     *      relates to.
     *
     * \see
     *      Plop_LoggerInterface::getFile() and
     *      Plop_LoggerInterface::getClass().
     */
    public function getMethod();

    /**
     * Log a message with the Plop::DEBUG log level.
     *
     * \param string $msg
     *      The message to log (possibly with values from
     *      the \a $args parameter embedded -- read the rest
     *      of this entry for more information).
     *
     * \param array $args
     *      (optional) Associative array of values that may
     *      be replaced dynamically in the log message,
     *      using a formatting sequence based on this model:
     *      \verbatim %(name)<spec> \endverbatim
     *      where \a name is the key of the variable, taken from
     *      the \a $args parameter, and \a \<spec\> is a valid
     *      <a href="http://php.net/sprintf">sprintf()</a> format
     *      specification, such as \verbatim %(foo)04d \endverbatim
     *      By default, an empty array is used.
     *
     * \param Exception|NULL $exception
     *      (optional) An exception that should also be logged.
     *      The exception's stack trace will be included in the
     *      final message.
     *
     * \retval Plop_LoggerInterface
     *      The logger instance (ie. \a $this).
     */
    public function debug(
                    $msg,
        array       $args = array(),
        Exception   $exception = NULL
    );

    /**
     * Log a message with the Plop::INFO log level.
     *
     * \param string $msg
     *      The message to log (possibly with values from
     *      the \a $args parameter embedded -- read the rest
     *      of this entry for more information).
     *
     * \param array $args
     *      (optional) Associative array of values that may
     *      be replaced dynamically in the log message,
     *      using a formatting sequence based on this model:
     *      \verbatim %(name)<spec> \endverbatim
     *      where \a name is the key of the variable, taken from
     *      the \a $args parameter, and \a \<spec\> is a valid
     *      <a href="http://php.net/sprintf">sprintf()</a> format
     *      specification, such as \verbatim %(foo)04d \endverbatim
     *      By default, an empty array is used.
     *
     * \param Exception|NULL $exception
     *      (optional) An exception that should also be logged.
     *      The exception's stack trace will be included in the
     *      final message.
     *
     * \retval Plop_LoggerInterface
     *      The logger instance (ie. \a $this).
     */
    public function info(
                    $msg,
        array       $args = array(),
        Exception   $exception = NULL
    );

    /**
     * Log a message with the Plop::WARNING log level.
     *
     * \param string $msg
     *      The message to log (possibly with values from
     *      the \a $args parameter embedded -- read the rest
     *      of this entry for more information).
     *
     * \param array $args
     *      (optional) Associative array of values that may
     *      be replaced dynamically in the log message,
     *      using a formatting sequence based on this model:
     *      \verbatim %(name)<spec> \endverbatim
     *      where \a name is the key of the variable, taken from
     *      the \a $args parameter, and \a \<spec\> is a valid
     *      <a href="http://php.net/sprintf">sprintf()</a> format
     *      specification, such as \verbatim %(foo)04d \endverbatim
     *      By default, an empty array is used.
     *
     * \param Exception|NULL $exception
     *      (optional) An exception that should also be logged.
     *      The exception's stack trace will be included in the
     *      final message.
     *
     * \retval Plop_LoggerInterface
     *      The logger instance (ie. \a $this).
     */
    public function warning(
                    $msg,
        array       $args = array(),
        Exception   $exception = NULL
    );

    /**
     * Log a message with the Plop::WARN log level.
     *
     * \param string $msg
     *      The message to log (possibly with values from
     *      the \a $args parameter embedded -- read the rest
     *      of this entry for more information).
     *
     * \param array $args
     *      (optional) Associative array of values that may
     *      be replaced dynamically in the log message,
     *      using a formatting sequence based on this model:
     *      \verbatim %(name)<spec> \endverbatim
     *      where \a name is the key of the variable, taken from
     *      the \a $args parameter, and \a \<spec\> is a valid
     *      <a href="http://php.net/sprintf">sprintf()</a> format
     *      specification, such as \verbatim %(foo)04d \endverbatim
     *      By default, an empty array is used.
     *
     * \param Exception|NULL $exception
     *      (optional) An exception that should also be logged.
     *      The exception's stack trace will be included in the
     *      final message.
     *
     * \retval Plop_LoggerInterface
     *      The logger instance (ie. \a $this).
     */
    public function warn(
                    $msg,
        array       $args = array(),
        Exception   $exception = NULL
    );

    /**
     * Log a message with the Plop::ERROR log level.
     *
     * \param string $msg
     *      The message to log (possibly with values from
     *      the \a $args parameter embedded -- read the rest
     *      of this entry for more information).
     *
     * \param array $args
     *      (optional) Associative array of values that may
     *      be replaced dynamically in the log message,
     *      using a formatting sequence based on this model:
     *      \verbatim %(name)<spec> \endverbatim
     *      where \a name is the key of the variable, taken from
     *      the \a $args parameter, and \a \<spec\> is a valid
     *      <a href="http://php.net/sprintf">sprintf()</a> format
     *      specification, such as \verbatim %(foo)04d \endverbatim
     *      By default, an empty array is used.
     *
     * \param Exception|NULL $exception
     *      (optional) An exception that should also be logged.
     *      The exception's stack trace will be included in the
     *      final message.
     *
     * \retval Plop_LoggerInterface
     *      The logger instance (ie. \a $this).
     */
    public function error(
                    $msg,
        array       $args = array(),
        Exception   $exception = NULL
    );

    /**
     * Log a message with the Plop::CRITICAL log level.
     *
     * This is an alias for Plop_LoggerInterface::fatal().
     *
     * \param string $msg
     *      The message to log (possibly with values from
     *      the \a $args parameter embedded -- read the rest
     *      of this entry for more information).
     *
     * \param array $args
     *      (optional) Associative array of values that may
     *      be replaced dynamically in the log message,
     *      using a formatting sequence based on this model:
     *      \verbatim %(name)<spec> \endverbatim
     *      where \a name is the key of the variable, taken from
     *      the \a $args parameter, and \a \<spec\> is a valid
     *      <a href="http://php.net/sprintf">sprintf()</a> format
     *      specification, such as \verbatim %(foo)04d \endverbatim
     *      By default, an empty array is used.
     *
     * \param Exception|NULL $exception
     *      (optional) An exception that should also be logged.
     *      The exception's stack trace will be included in the
     *      final message.
     *
     * \retval Plop_LoggerInterface
     *      The logger instance (ie. \a $this).
     */
    public function critical(
                    $msg,
        array       $args = array(),
        Exception   $exception = NULL
    );

    /**
     * Log a message with the Plop::CRITICAL log level.
     *
     * This is an alias for Plop_LoggerInterface::critical().
     *
     * \param string $msg
     *      The message to log (possibly with values from
     *      the \a $args parameter embedded -- read the rest
     *      of this entry for more information).
     *
     * \param array $args
     *      (optional) Associative array of values that may
     *      be replaced dynamically in the log message,
     *      using a formatting sequence based on this model:
     *      \verbatim %(name)<spec> \endverbatim
     *      where \a name is the key of the variable, taken from
     *      the \a $args parameter, and \a \<spec\> is a valid
     *      <a href="http://php.net/sprintf">sprintf()</a> format
     *      specification, such as \verbatim %(foo)04d \endverbatim
     *      By default, an empty array is used.
     *
     * \param Exception|NULL $exception
     *      (optional) An exception that should also be logged.
     *      The exception's stack trace will be included in the
     *      final message.
     *
     * \retval Plop_LoggerInterface
     *      The logger instance (ie. \a $this).
     */
    public function fatal(
                    $msg,
        array       $args = array(),
        Exception   $exception = NULL
    );

    /**
     * Log an exception.
     *
     * \param string $msg
     *      The message to log (possibly with values from
     *      the \a $args parameter embedded -- read the rest
     *      of this entry for more information).
     *
     * \param Exception $exception
     *      An exception that should also be logged.
     *      The exception's stack trace will be included in the
     *      final message.
     *
     * \param array $args
     *      (optional) Associative array of values that may
     *      be replaced dynamically in the log message,
     *      using a formatting sequence based on this model:
     *      \verbatim %(name)<spec> \endverbatim
     *      where \a name is the key of the variable, taken from
     *      the \a $args parameter, and \a \<spec\> is a valid
     *      <a href="http://php.net/sprintf">sprintf()</a> format
     *      specification, such as \verbatim %(foo)04d \endverbatim
     *      By default, an empty array is used.
     *
     * \retval Plop_LoggerInterface
     *      The logger instance (ie. \a $this).
     *
     * \note
     *      The message containing the exception will be
     *      logged with the Plop::ERROR log level.
     *      To log it using another log level, call another
     *      logging method and pass the exception as the
     *      value for the \a $exception argument.
     */
    public function exception(
                    $msg,
        Exception   $exception,
        array       $args = array()
    );

    /**
     * Log a message with the given log level.
     *
     * \param int $level
     *      The log level for the message. This argument
     *      is only useful for custom log levels.
     *      For pre-defined levels, you should consider
     *      using the following shortcut methods:
     *      -   Plop_LoggerInterface::debug()
     *      -   Plop_LoggerInterface::info()
     *      -   Plop_LoggerInterface::warning()
     *      -   Plop_LoggerInterface::error()
     *      -   Plop_LoggerInterface::critical()
     *      -   Plop_LoggerInterface::fatal()
     *
     * \param string $msg
     *      The message to log (possibly with values from
     *      the \a $args parameter embedded -- read the rest
     *      of this entry for more information).
     *
     * \param array $args
     *      (optional) Associative array of values that may
     *      be replaced dynamically in the log message,
     *      using a formatting sequence based on this model:
     *      \verbatim %(name)<spec> \endverbatim
     *      where \a name is the key of the variable, taken from
     *      the \a $args parameter, and \a \<spec\> is a valid
     *      <a href="http://php.net/sprintf">sprintf()</a> format
     *      specification, such as \verbatim %(foo)04d \endverbatim
     *      By default, an empty array is used.
     *
     * \param Exception|NULL $exception
     *      (optional) An exception that should also be logged.
     *      The exception's stack trace will be included in the
     *      final message.
     *
     * \retval Plop_LoggerInterface
     *      The logger instance (ie. \a $this).
     */
    public function log(
                    $level,
                    $msg,
        array       $args       = array(),
        Exception   $exception  = NULL
    );

    /**
     * Associate a new handler with this logger.
     *
     * \param Plop_HandlerInterface $handler
     *      The new handler to associate with this logger.
     *
     * \retval Plop_LoggerInterface
     *      The logger instance (ie. \a $this).
     */
    public function addHandler(Plop_HandlerInterface $handler);

    /**
     * Deassociate a handler with this logger.
     *
     * \param Plop_HandlerInterface $handler
     *      The handler to deassociate with this logger.
     *
     * \retval Plop_LoggerInterface
     *      The logger instance (ie. \a $this).
     */
    public function removeHandler(Plop_HandlerInterface $handler);

    /**
     * Return a list of handlers currently
     * associated with this logger.
     *
     * \retval array
     *      List of handlers currently associated
     *      with this logger.
     */
    public function getHandlers();
}

