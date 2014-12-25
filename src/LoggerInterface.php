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
 * \brief
 *      Interface for a logger.
 */
interface LoggerInterface
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
     * \param int|string $level
     *      The new minimum level at which log records
     *      will be accepted, either as an integer
     *      or as a level name.
     *
     * \retval Plop::LoggerInterface
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
     *      \a true if a log with the given level would
     *      be handled by this instance, or \a false
     *      if it would be ignored.
     */
    public function isEnabledFor($level);

    /**
     * Return the name of the namespace this logger
     * applies to.
     *
     * \retval string
     *      Name of the namespace this logger relates to.
     *
     * \see
     *      Plop::LoggerInterface::getClass() and
     *      Plop::LoggerInterface::getMethod().
     */
    public function getNamespace();

    /**
     * Return the name of the class this logger
     * applies to.
     *
     * \retval string
     *      Name of the class this logger relates to.
     *
     * \see
     *      Plop::LoggerInterface::getNamespace() and
     *      Plop::LoggerInterface::getMethod().
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
     *      Plop::LoggerInterface::getNamespace() and
     *      Plop::LoggerInterface::getClass().
     */
    public function getMethod();

    /**
     * Return the factory used to produce
     * log records.
     *
     * \retval Plop::RecordFactoryInterface
     *      The factory used to produce records.
     */
    public function getRecordFactory();

    /**
     * Set the factory to use to produce
     * new log records.
     *
     * \param Plop::RecordFactoryInterface $factory
     *      The factory to use to produce records.
     *
     * \retval Plop::LoggerInterface
     *      The logger instance (ie. \a $this).
     */
    public function setRecordFactory(\Plop\RecordFactoryInterface $factory);

    /**
     * Get the collection of filters currently
     * associated with this logger.
     *
     * \retval Plop::FiltersCollectionAbstract
     *      Collection of filters associated with this logger.
     */
    public function getFilters();

    /**
     * Set the collection of filters associated
     * with this logger.
     *
     * \param Plop::FiltererInterface $filters
     *      New collection of filters to associate
     *      with this logger.
     *
     * \retval Plop::LoggerInterface
     *      The logger instance (ie. \a $this).
     */
    public function setFilters(\Plop\FiltersCollectionAbstract $filters);

    /**
     * Get the collection of handlers currently
     * associated with this logger.
     *
     * \retval Plop::HandlersCollectionAbstract
     *      Collection of handlers associated with this logger.
     */
    public function getHandlers();

    /**
     * Set the collection of handlers associated
     * with this logger.
     *
     * \param Plop::HandlersCollectionAbstract $handlers
     *      New collection of handlers to associate
     *      with this logger.
     *
     * \retval Plop::LoggerInterface
     *      The logger instance (ie. \a $this).
     */
    public function setHandlers(\Plop\HandlersCollectionAbstract $handlers);

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
     * \param Exception|null $exception
     *      (optional) An exception that should also be logged.
     *      The exception's stack trace will be included in the
     *      final message.
     *
     * \retval Plop::LoggerInterface
     *      The logger instance (ie. \a $this).
     */
    public function debug(
        $msg,
        array $args = array(),
        \Exception $exception = null
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
     * \param Exception|null $exception
     *      (optional) An exception that should also be logged.
     *      The exception's stack trace will be included in the
     *      final message.
     *
     * \retval Plop::LoggerInterface
     *      The logger instance (ie. \a $this).
     */
    public function info(
        $msg,
        array $args = array(),
        \Exception $exception = null
    );

    /**
     * Log a message with the Plop::NOTICE log level.
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
     * \param Exception|null $exception
     *      (optional) An exception that should also be logged.
     *      The exception's stack trace will be included in the
     *      final message.
     *
     * \retval Plop::LoggerInterface
     *      The logger instance (ie. \a $this).
     */
    public function notice(
        $msg,
        array $args = array(),
        \Exception $exception = null
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
     * \param Exception|null $exception
     *      (optional) An exception that should also be logged.
     *      The exception's stack trace will be included in the
     *      final message.
     *
     * \retval Plop::LoggerInterface
     *      The logger instance (ie. \a $this).
     */
    public function warning(
        $msg,
        array $args = array(),
        \Exception $exception = null
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
     * \param Exception|null $exception
     *      (optional) An exception that should also be logged.
     *      The exception's stack trace will be included in the
     *      final message.
     *
     * \retval Plop::LoggerInterface
     *      The logger instance (ie. \a $this).
     */
    public function warn(
        $msg,
        array $args = array(),
        \Exception $exception = null
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
     * \param Exception|null $exception
     *      (optional) An exception that should also be logged.
     *      The exception's stack trace will be included in the
     *      final message.
     *
     * \retval Plop::LoggerInterface
     *      The logger instance (ie. \a $this).
     */
    public function error(
        $msg,
        array $args = array(),
        \Exception $exception = null
    );

    /**
     * Log a message with the Plop::CRITICAL log level.
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
     * \param Exception|null $exception
     *      (optional) An exception that should also be logged.
     *      The exception's stack trace will be included in the
     *      final message.
     *
     * \retval Plop::LoggerInterface
     *      The logger instance (ie. \a $this).
     */
    public function critical(
        $msg,
        array $args = array(),
        \Exception $exception = null
    );

    /**
     * Log a message with the Plop::ALERT log level.
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
     * \param Exception|null $exception
     *      (optional) An exception that should also be logged.
     *      The exception's stack trace will be included in the
     *      final message.
     *
     * \retval Plop::LoggerInterface
     *      The logger instance (ie. \a $this).
     */
    public function alert(
        $msg,
        array $args = array(),
        \Exception $exception = null
    );

    /**
     * Log a message with the Plop::EMERGENCY log level.
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
     * \param Exception|null $exception
     *      (optional) An exception that should also be logged.
     *      The exception's stack trace will be included in the
     *      final message.
     *
     * \retval Plop::LoggerInterface
     *      The logger instance (ie. \a $this).
     */
    public function emergency(
        $msg,
        array $args = array(),
        \Exception $exception = null
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
     * \retval Plop::LoggerInterface
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
        \Exception $exception,
        array $args = array()
    );

    /**
     * Log a message with the given log level.
     *
     * \param int $level
     *      The log level for the message. This argument
     *      is only useful for custom log levels.
     *      For pre-defined levels, you should consider
     *      using the following shortcut methods:
     *      -   Plop::LoggerInterface::debug()
     *      -   Plop::LoggerInterface::info()
     *      -   Plop::LoggerInterface::notice()
     *      -   Plop::LoggerInterface::warning()
     *      -   Plop::LoggerInterface::error()
     *      -   Plop::LoggerInterface::critical()
     *      -   Plop::LoggerInterface::alert()
     *      -   Plop::LoggerInterface::emergency()
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
     * \param Exception|null $exception
     *      (optional) An exception that should also be logged.
     *      The exception's stack trace will be included in the
     *      final message.
     *
     * \retval Plop::LoggerInterface
     *      The logger instance (ie. \a $this).
     */
    public function log(
        $level,
        $msg,
        array $args = array(),
        \Exception $exception = null
    );
}
