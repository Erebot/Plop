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

/// No log level defined.
const NOTSET    =  0;

/// Detailed debug information.
const DEBUG     = 10;

/**
 * Interesting events.
 *
 * Example: User logs in, SQL logs.
 */
const INFO      = 20;

/// Normal but significant events.
const NOTICE    = 30;

/**
 * Exceptional occurrences that are not errors.
 *
 * Example: Use of deprecated APIs, poor use of an API, undesirable things
 * that are not necessarily wrong.
 */
const WARNING   = 40;

/// Alias for Plop::WARNING.
const WARN      = \Plop\WARNING;

/**
 * Runtime errors that do not require immediate action but should typically
 * be logged and monitored.
 */
const ERROR     = 50;

/**
 * Critical conditions.
 *
 * Example: Application component unavailable, unexpected exception.
 */
const CRITICAL  = 60;

/**
 * Action must be taken immediately.
 *
 * Example: Entire website down, database unavailable, etc. This should
 * trigger the SMS alerts and wake you up.
 */
const ALERT     = 70;

/// System is unusable.
const EMERGENCY = 80;


/**
 *  \brief
 *      Main class for Plop.
 *
 *  For the most basic use cases, the Plop class acts
 *  as an instance of Plop::LoggerInterface, which means
 *  you only need code such as the following to start
 *  logging messages:
 *  \code
 *      // Grab an instance of the logging service.
 *      $logging = Plop::getInstance();
 *      // Log a message with the INFO level.
 *      $logging->info('The cat is both dead and alive!');
 *  \endcode
 *
 *  This is equivalent to the following less concise
 *  piece of code:
 *  \code
 *      $logging = Plop::getInstance();
 *      $logger = $logging->getLogger(__NAMESPACE__, __CLASS__, __FUNCTION__);
 *      $logger->info('The cat is both dead and alive!');
 *  \endcode
 *
 *  For more complex use cases, you will need to configure
 *  logging (either manually or by means of tools such as a
 *  <a href="https://github.com/Erebot/DependencyInjection">Dependency
 *  Injection Container</a>).
 *  The following piece of code shows how to configure bits of Plop
 *  using PHP code:
 *  \code
 *      $logging = \\Plop\\Plop::getInstance();
 *
 *      // Grab the root logger.
 *      $logger = $logging->getLogger();
 *
 *      // Log only messages with a level of INFO or more.
 *      $logger->setLevel(\\Plop::INFO);
 *
 *      // Change the format used to display logs on the console.
 *      // Also, display dates as UNIX timestamps instead of using
 *      // the default format ("2012-09-29 11:16:15,123").
 *      foreach ($logger->getHandlers() as $handler)
 *          $handler->getFormatter()
 *              ->setFormat('%(asctime)s - %(levelname)s - %(message)s')
 *              ->setDateFormat('U'); // We want UNIX timestamps.
 *
 *      // Send logs to the syslog (using the default format),
 *      // in addition to the console, if the level is WARNING
 *      // or above.
 *      $handler = new \\Plop\\Handler\\SysLog(
 *          \\Plop\\Handler\\SysLog::DEFAULT_ADDRESS,
 *          LOG_DAEMON
 *      );
 *      $handlers = $logger->getHandlers();
 *      $handlers[] = $handler->setLevel(\\Plop::WARNING);
 *  \endcode
 */
class Plop extends \Plop\IndirectLoggerAbstract implements \ArrayAccess, \Countable
{
    /// Default format used by the root logger.
    const BASIC_FORMAT  = '[%(levelname)s] %(message)s';

    /// Shared instance of the logging service.
    static protected $instance = null;

    /// Associative array of loggers, indexed by their ID.
    protected $loggers;
    /// Mapping between level names and their value.
    protected $levelNames;
    /// Date and time when the logging service was initialized.
    protected $created;

    /**
     * Create a new instance of the logging service.
     */
    protected function __construct()
    {
        $this->loggers      = array();
        $rootLogger         = new \Plop\Logger(null, null);
        $basicHandler       = new \Plop\Handler\Stream(fopen('php://stderr', 'w'));
        $this[]             = $rootLogger;
        $handlers           = $rootLogger->getHandlers();
        $formatter          = new \Plop\Formatter(self::BASIC_FORMAT);
        $handlers[]         = $basicHandler->setFormatter($formatter);
        $this->created      = microtime(true);
        $this->levelNames   = array(
            \Plop\NOTSET    => 'NOTSET',
            \Plop\DEBUG     => 'DEBUG',
            \Plop\INFO      => 'INFO',
            \Plop\WARNING   => 'WARNING',
            \Plop\ERROR     => 'ERROR',
            \Plop\CRITICAL  => 'CRITICAL',
        );
    }

    /// This class is not clone-safe.
    public function __clone()
    {
        throw new \Plop\Exception('Cloning this class is forbidden');
    }

    /**
     * Return an instance of the logging service.
     *
     * \retval Plop
     *      Instance of the logging service.
     */
    public static function & getInstance()
    {
        if (static::$instance === null) {
            $c = __CLASS__;
            static::$instance = new $c();
        }
        return static::$instance;
    }

    /**
     * Get the date and time (as a UNIX timestamp
     * with microseconds resolution) when the logging
     * service was created.
     *
     * \retval float
     *      Creation date of the logging service.
     */
    public function getCreationDate()
    {
        return $this->created;
    }

    /**
     * Set a (new) name for a given level.
     *
     * \param string $levelName
     *      Name of the new level to register.
     *
     * \param int $levelValue
     *      Value for the new level.
     *
     * \retval Plop
     *      The current logging service (ie. \a $this).
     */
    public function addLevelName($levelName, $levelValue)
    {
        if (!is_int($levelValue)) {
            throw new \Plop\Exception('Invalid level value');
        }
        if (!is_string($levelName)) {
            throw new \Plop\Exception('Invalid level name');
        }
        $this->levelNames[$levelValue] = $levelName;
        return $this;
    }

    /**
     * Return the name of a level given its value.
     *
     * \param int $level
     *      Level for which a name must be returned.
     *
     * \retval string
     *      Name for the given level.
     *
     * \note
     *      If the level was not given a specific name
     *      (ie. Plop::addLevelName() was not called first),
     *      "Level $level" is returned.
     */
    public function getLevelName($level)
    {
        if (!is_int($level)) {
            throw new \Plop\Exception('Invalid level value');
        }
        if (!isset($this->levelNames[$level])) {
            return "Level $level";
        }
        return $this->levelNames[$level];
    }

    /**
     * Return the value of a level given its name.
     *
     * \param string $levelName
     *      Level for which a value must be returned.
     *
     * \retval int
     *      Value for the given level.
     *
     * \note
     *      If the given level name is not known,
     *      Plop::NOTSET (0) is returned.
     *
     * \note
     *      You may use Plop::addLevelName() to register
     *      new levels.
     */
    public function getLevelValue($levelName)
    {
        if (!is_string($levelName)) {
            throw new \Plop\Exception('Invalid level name');
        }
        $key = array_search($levelName, $this->levelNames, true);
        return (int) $key; // false is silently converted to 0.
    }

    /**
     * Return the logger that is most appropriate
     * given a bit of context.
     *
     * \param string $namespace
     *      (optional) Namespace for which a logger
     *      must be returned.
     *      Most of the time, you will pass the value
     *      of \a \_\_NAMESPACE\_\_ to this parameter.
     *
     * \param string $class
     *      (optional) Class for which a logger must be
     *      returned.
     *      Most of the time, you will pass the value
     *      of \a \_\_CLASS\_\_ to this parameter.
     *
     * \param string $method
     *      (optional) Method inside the given class
     *      for which a logger must be returned.
     *      Most of the time, you will pass the value
     *      of \a \_\_FUNCTION\_\_ to this parameter,
     *      even for methods, where this will have
     *      the same value as \a \_\_FUNCTION\_\_.
     *
     * \retval Plop::LoggerInterface
     *      Logger that is the most appropriate given
     *      the context.
     *
     * \note
     *      For functions, pass \a null as the value
     *      for the \a $class parameter.
     *
     * \warning
     *      When the default value is kept for every
     *      parameter, this method will return the root
     *      logger. It will not try to get the values
     *      of \a \_\_NAMESPACE\_\_, \a \_\_CLASS\_\_
     *      and \a \_\_FUNCTION\_\_ automatically.
     *      If you need more magic than that, keep in mind
     *      that the Plop class also implements the
     *      Plop::LoggerInterface interface to provide
     *      shortcuts.
     *      Therefore,
     *      \code
     *          $logging->info('The quick brown fox jumps over the lazy dog');
     *      \endcode
     *      is equivalent to
     *      \code
     *          $logging
     *              ->getLogger(\_\_NAMESPACE\_\_, \_\_CLASS\_\_, \_\_FUNCTION\_\_)
     *              ->info('The quick brown fox jumps over the lazy dog');
     *      \endcode
     */
    public function getLogger($namespace = '', $class = '', $method = '')
    {
        // Remove any potential namespace from the class and method names.
        $class = substr($class, strrpos('\\' . $class, '\\'));
        $method = substr($method, strrpos('\\' . $method, '\\'));

        // If __METHOD__ was used instead of __FUNCTION__, it also contains
        // the class name as a prefix. We get rid of that too.
        $method = substr((string) $method, strrpos(':' . $method, ':'));

        return $this["$method:$class:$namespace"];
    }

    /**
     * Register a logger.
     *
     * This is effectively a shortcut for the following piece
     * of code:
     * \code
     *      $logging[] = $logger;
     * \endcode
     * It is kept to help other tools that operate on Plop
     * (such as Dependency Injection Containers) but do not
     * support object subscripting (ie. array notation).
     *
     * \param Plop::LoggerInterface $logger
     *      New logger to register.
     *
     * \retval Plop
     *      The current logging service (ie. \a $this).
     *
     * \note
     *      Since the Plop class acts as a singleton,
     *      any logger registered with this method
     *      can be retrieved later by calling
     *      \code
     *          (Plop::getInstance())->getLogger();
     *      \endcode
     *      with the appropriate arguments.
     *
     * \note
     *      You may register several loggers at the same
     *      time with this method. Just pass each logger
     *      to register as an argument to this method.
     */
    public function addLogger(\Plop\LoggerInterface $logger /*, ... */)
    {
        $loggers = func_get_args();
        foreach ($loggers as $logger) {
            if (!($logger instanceof \Plop\LoggerInterface)) {
                throw new \Plop\Exception('Not a logger');
            }
        }

        foreach ($loggers as $logger) {
            $this[] = $logger;
        }
        return $this;
    }

    /**
     * Return a logger's identifier.
     *
     * \param Plop::LoggerInterface $logger
     *      A logger whose identifier we're interested in.
     *
     * \retval string
     *      The logger's identifier.
     */
    protected static function getLoggerId(\Plop\LoggerInterface $logger)
    {
        $func   = $logger->getMethod();
        $cls    = $logger->getClass();
        $ns     = $logger->getNamespace();
        return "$func:$cls:$ns";
    }

    /**
     * Return the number of loggers currently
     * registered with Plop.
     *
     * \retval int
     *      Number of loggers currently registered.
     */
    public function count()
    {
        return count($this->loggers);
    }

    /**
     * Register a new logger with Plop.
     *
     * \param mixed $offset
     *      (deprecated) Identifier for the logger,
     *      must match the identifier of the logger
     *      given in \a $logger.
     *
     * \param Plop::LoggerInterface $logger
     *      New logger to register.
     *
     * \deprecated
     *      The \a $offset argument is deprecated as
     *      Plop already deduces the value automatically
     *      from the \a $logger argument.
     *
     * \note
     *      If a logger already exists with the given identifier,
     *      it will be replaced by the new one.
     *
     * \note
     *      The usual pattern for registering new loggers is
     *      \code
     *          $logging[] = $logger;
     *      \endcode
     *      Also, Plop::addLogger() is an alias for that pattern.
     */
    public function offsetSet($offset, $logger)
    {
        if (!($logger instanceof \Plop\LoggerInterface)) {
            throw new \Plop\Exception('Invalid logger');
        }

        $id = static::getLoggerId($logger);
        if (is_string($offset)) {
            if ($offset != $id) {
                throw new \Plop\Exception('Identifier mismatch');
            }
        }

        $this->loggers[$id] = $logger;
    }

    /**
     * Return the registered logger with the given identifier,
     * one of its parents, or the root logger if no other logger
     * can be found.
     *
     * \param string $offset
     *      Identifier of the logger to return.
     *
     * \retval Plop::LoggerInterface
     *      The registered logger with that identifier if one
     *      was found, or the root logger.
     *
     * \warning
     *      Do not call this method directly, use
     *      Plop::getLogger() instead.
     */
    public function offsetGet($offset)
    {
        if (!is_string($offset)) {
            throw new \Plop\Exception('Invalid identifier');
        }

        $parts = explode(':', $offset, 3);
        if (count($parts) != 3) {
            throw new \Plop\Exception('Invalid identifier');
        }
        list($method, $class, $ns) = $parts;

        $len = -strlen('\\');
        while (substr($ns, $len) == '\\') {
            $ns = (string) substr($ns, 0, $len);
        }

        // Namespace, class and method/function match.
        if (isset($this->loggers["$method:$class:$ns"])) {
            return $this->loggers["$method:$class:$ns"];
        }

        // Namespace and class match.
        if ($class != "" && isset($this->loggers[":$class:$ns"])) {
            return $this->loggers[":$class:$ns"];
        }

        // Namespace match.
        $parts = explode('\\', $ns);
        while (count($parts)) {
            $offset = implode('\\', $parts);
            if ($offset == '') {
                break;
            }

            if (isset($this->loggers["::$offset"])) {
                return $this->loggers["::$offset"];
            }
            array_pop($parts);
        }

        // Root logger.
        return $this->loggers['::'];
    }

    /**
     * Return a flag indicating whether a logger with
     * the given identifier was registered with Plop.
     *
     * \param string|Plop::LoggerInterface $offset
     *      A logger identifier. You may also pass a logger,
     *      in which case, that logger's identifier will be
     *      used for the test.
     *
     * \retval bool
     *      A flag indicating whether a logger was registered
     *      with that identifier (\a true) or not (\a false).
     *
     * \warning
     *      When a logger is passed to this method, it will
     *      only look for a registered logger with the same
     *      identifier. It will not check whether both loggers
     *      are actually the same.
     */
    public function offsetExists($offset)
    {
        if ($offset instanceof \Plop\LoggerInterface) {
            $offset = static::getLoggerId($offset);
        }
        if (!is_string($offset)) {
            throw new \Plop\Exception('Invalid identifier');
        }
        return isset($this->loggers[$offset]);
    }

    /**
     * Unregister a logger.
     *
     * \param string|Plop::LoggerInterface $offset
     *      Identifier of the logger to unregister.
     *      You may also pass a logger, in which case,
     *      that logger's identifier will be used.
     *
     * \warning
     *      When a logger is passed to this method, it will
     *      only look for a registered logger with the same
     *      identifier. It will not check whether both loggers
     *      are actually the same.
     */
    public function offsetUnset($offset)
    {
        if ($offset instanceof \Plop\LoggerInterface) {
            $offset = static::getLoggerId($offset);
        }
        if ($offset == "::") {
            throw new \Plop\Exception('The root logger cannot be unset');
        }
        unset($this->loggers[$offset]);
    }

    /// \copydoc Plop::IndirectLoggerAbstract::getIndirectLogger().
    protected function getIndirectLogger()
    {
        $caller = static::findCaller();
        return $this["{$caller['func']}:{$caller['cls']}:{$caller['ns']}"];
    }

    /**
     * Return information about the caller of this method.
     *
     * \retval array
     *      An associative array with information about the caller.
     *      This array always contains the following keys:
     *      -   "ns" -- the name of the caller's namespace.
     *      -   "dir" -- the path to the caller's file.
     *      -   "file" -- the name of the caller's file.
     *      -   "line" -- the line number in that file where the call
     *          was made.
     *      -   "func" -- the name of the function/method where the
     *          call happened.
     *      -   "cls" -- the name of the class where the call was
     *          made.
     *
     *      Each of those values may be null (or 0 in the case of
     *      "line") if the information could not be extracted from
     *      the call stack.
     *
     * \note
     *      Here, "caller" means the first context in the call stack
     *      that does not refer to one of Plop's methods/files.
     */
    public static function findCaller()
    {
        if (version_compare(PHP_VERSION, '5.3.6', '>=')) {
            $bt = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        } else {
            $bt = debug_backtrace(false);
        }

        $max    = count($bt);
        $ns     = __NAMESPACE__ . '\\';
        $len    = strlen($ns);
        $ns2    = $ns . 'Tests' . '\\';
        for ($i = 0; $i < $max; $i++) {
            if (isset($bt[$i]['function']) &&
                !strncmp($ns, $bt[$i]['function'], $len) &&
                strncmp($ns2, $bt[$i]['function'], $len + 6)) {
                // Skip frames until we get out of Plop's code.
                continue;
            }

            if (isset($bt[$i]['class']) &&
                !strncmp($ns, $bt[$i]['class'], $len) &&
                strncmp($ns2, $bt[$i]['class'], $len + 6)) {
                // Skip frames until we get out of Plop's code.
                continue;
            }

            break;
        }

        if ($i == $max) {
            return array(
                'ns'    => null,
                'file'  => null,
                'line'  => 0,
                'func'  => null,
                'cls'   => null,
            );
        }

        $ns     = '';
        $func   = isset($bt[$i]['function']) ? $bt[$i]['function'] : null;
        $cls    = isset($bt[$i]['class']) ? $bt[$i]['class'] : null;
        $file   = isset($bt[$i - 1]['file']) ? $bt[$i - 1]['file'] : null;
        $line   = isset($bt[$i - 1]['line']) ? $bt[$i - 1]['line'] : 0;

        if (($pos = strrpos($func, '\\')) !== false) {
            $ns     = substr($func, 0, $pos);
            $func   = substr($func, $pos + 1);
        } elseif (($pos = strrpos($cls, '\\')) !== false) {
            $ns     = substr($cls, 0, $pos);
            $cls    = substr($cls, $pos + 1);
        }

        return array(
            'ns'    => $ns,
            'file'  => $file,
            'line'  => $line,
            'func'  => $func,
            'cls'   => $cls,
        );
    }
}
