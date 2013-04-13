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
 *      Main class for Plop.
 *
 *  For the most basic use cases, the Plop class acts
 *  as an instance of Plop_LoggerInterface, which means
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
 *      $logger = $logging->getLogger(__FILE__, __CLASS__, __FUNCTION__);
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
 *      $logging = Plop::getInstance();
 *
 *      // Grab the root logger.
 *      $logger = $logging->getLogger();
 *
 *      // Log only messages with a level of INFO or more.
 *      $logger->setLevel(Plop::INFO);
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
 *      $handler = new Plop_Handler_SysLog(
 *          Plop_Handler_SysLog::DEFAULT_ADDRESS,
 *          LOG_DAEMON
 *      );
 *      $handlers = $logger->getHandlers();
 *      $handlers[] = $handler->setLevel(Plop::WARNING);
 *  \endcode
 */
class       Plop
extends     Plop_IndirectLoggerAbstract
implements  ArrayAccess,
            Countable
{
    /// Default format used by the root logger.
    const BASIC_FORMAT  = '[%(levelname)s] %(message)s';

    /// No log level defined.
    const NOTSET    =  0;
    /// DEBUG log level.
    const DEBUG     = 10;
    /// INFO log level.
    const INFO      = 20;
    /// WARNING log level.
    const WARNING   = 30;
    /// Alias for the WARNING log level.
    const WARN      = 30;
    /// ERROR log level.
    const ERROR     = 40;
    /// CRITICAL error log level.
    const CRITICAL  = 50;

    /// Shared instance of the logging service.
    static protected $_instance = NULL;

    /// Associative array of loggers, indexed by their ID.
    protected $_loggers;
    /// Mapping between level names and their value.
    protected $_levelNames;
    /// Date and time when the logging service was initialized.
    protected $_created;
    /// An object responsible for removing common prefixes from files.
    protected $_prefixStripper;

    /**
     * Create a new instance of the logging service.
     */
    protected function __construct()
    {
        $this->_loggers = array();
        $rootLogger     = new Plop_Logger(NULL, NULL, NULL);
        $basicHandler   = new Plop_Handler_Stream(fopen('php://stderr', 'w'));
        $this[]         = $rootLogger;
        $handlers       = $rootLogger->getHandlers();
        $handlers[]     = $basicHandler->setFormatter(
                            new Plop_Formatter(self::BASIC_FORMAT)
                        );
        $this->_levelNames = array(
            self::NOTSET    => 'NOTSET',
            self::DEBUG     => 'DEBUG',
            self::INFO      => 'INFO',
            self::WARNING   => 'WARNING',
            self::ERROR     => 'ERROR',
            self::CRITICAL  => 'CRITICAL',
        );
        $this->_created         = microtime(TRUE);
        $this->_prefixStripper  = new Plop_PrefixesCollection();
    }

    /// This class is not clone-safe.
    public function __clone()
    {
        throw new Plop_Exception('Cloning this class is forbidden');
    }

    /**
     * Return an instance of the logging service.
     *
     * \retval Plop
     *      Instance of the logging service.
     */
    static public function & getInstance()
    {
        if (self::$_instance === NULL) {
            $c = __CLASS__;
            self::$_instance = new $c();
        }
        return self::$_instance;
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
        return $this->_created;
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
            throw new Plop_Exception('Invalid level value');
        }
        if (!is_string($levelName)) {
            throw new Plop_Exception('Invalid level name');
        }
        $this->_levelNames[$levelValue] = $levelName;
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
            throw new Plop_Exception('Invalid level value');
        }
        if (!isset($this->_levelNames[$level])) {
            return "Level $level";
        }
        return $this->_levelNames[$level];
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
            throw new Plop_Exception('Invalid level name');
        }
        $key = array_search($levelName, $this->_levelNames, TRUE);
        return (int) $key; // FALSE is silently converted to 0.
    }

    public function getPrefixes()
    {
        return $this->_prefixStripper;
    }

    public function setPrefixes(Plop_PrefixesCollectionInterface $prefixes)
    {
        $this->_prefixStripper = $prefixes;
        return $this;
    }

    /**
     * Return the logger that is most appropriate
     * given a bit of context.
     *
     * \param string $file
     *      (optional) Name of the file for which
     *      a logger must be returned.
     *      Most of the time, you will pass the value
     *      of \a \_\_FILE\_\_ to this parameter.
     *
     * \param string $class
     *      (optional) Class inside the given file
     *      for which a logger must be returned.
     *      Most of the time, you will pass the value
     *      of \a \_\_CLASS\_\_ to this parameter.
     *
     * \param string $method
     *      (optional) Method inside the given class
     *      for which a logger must be returned.
     *      Most of the time, you will pass the value
     *      of \a \_\_FUNCTION\_\_ to this parameter,
     *      even for methods, where this will have
     *      the same value as \a \_\_METHOD\_\_.
     *
     * \retval Plop_LoggerInterface
     *      Logger that is the most appropriate given
     *      the context.
     *
     * \note
     *      For functions, pass \a NULL as the value
     *      for the \a $class parameter.
     *
     * \warning
     *      When the default value is kept for every
     *      parameter, this method will return the root
     *      logger. It will not try to get the values
     *      of \a \_\_FILE\_\_, \a \_\_CLASS\_\_ and
     *      \a \_\_FUNCTION\_\_ automatically.
     *      If you need more magic than that, keep in mind
     *      that the Plop class also implements the
     *      Plop_LoggerInterface interface to provide
     *      shortcuts.
     *      Therefore,
     *      \code
     *          $logging->info('The quick brown fox jumps over the lazy dog');
     *      \endcode
     *      is equivalent to
     *      \code
     *          $logging
     *              ->getLogger(\_\_FILE\_\_, \_\_CLASS\_\_, \_\_FUNCTION\_\_)
     *              ->info('The quick brown fox jumps over the lazy dog');
     *      \endcode
     */
    public function getLogger($file = '', $class = '', $method = '')
    {
        return $this["$method:$class:$file"];
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
     * \param Plop_LoggerInterface $logger
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
    public function addLogger(Plop_LoggerInterface $logger /*, ... */)
    {
        $loggers = func_get_args();
        foreach ($loggers as $logger) {
            if (!($logger instanceof Plop_LoggerInterface)) {
                throw new Plop_Exception('Not a logger');
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
     * \param Plop_LoggerInterface $logger
     *      A logger whose identifier we're interested in.
     *
     * \retval string
     *      The logger's identifier.
     */
    static protected function _getLoggerId(Plop_LoggerInterface $logger)
    {
        $method = $logger->getMethod();
        $class  = $logger->getClass();
        $file   = $logger->getFile();
        return "$method:$class:$file";
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
        return count($this->_loggers);
    }

    /**
     * Register a new logger with Plop.
     *
     * \param mixed $offset
     *      (deprecated) Identifier for the logger,
     *      must match the identifier of the logger
     *      given in \a $logger.
     *
     * \param Plop_LoggerInterface $logger
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
        if (!($logger instanceof Plop_LoggerInterface)) {
            throw new Plop_Exception('Invalid logger');
        }

        $id = self::_getLoggerId($logger);
        if (is_string($offset)) {
            if ($offset != $id) {
                throw new Plop_Exception('Identifier mismatch');
            }
        }

        /* Either :
         * - $file == NULL, $class == NULL, $method == NULL (root logger),
         * - $file == NULL, $class != NULL, $method == NULL (class logger),
         * - $file == NULL, $class == NULL, $method != NULL (function logger),
         * - $file == NULL, $class != NULL, $method != NULL (method logger),
         * - $file != NULL, $class == $method == NULL (file/directory logger).
         */
        $file   = $logger->getFile();
        $class  = $logger->getClass();
        $method = $logger->getMethod();
        if ($file !== NULL and ($class !== NULL or $method !== NULL)) {
            throw new Plop_Exception('$class and $method must both be NULL ' .
                                     'when $file is not NULL');
        }

        $this->_loggers[$id] = $logger;
    }

    /**
     * Return the registered logger with the given identifier,
     * or the root logger if no other logger was found.
     *
     * \param string $offset
     *      Identifier of the logger to return.
     *
     * \retval Plop_LoggerInterface
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
            throw new Plop_Exception('Invalid identifier');
        }

        $parts = explode(':', $offset, 3);
        if (count($parts) != 3) {
            throw new Plop_Exception('Invalid identifier');
        }
        list($method, $class, $file) = $parts;
        $file = $this->_prefixStripper->stripLongestPrefix($file);

        while (substr($file, -strlen(DIRECTORY_SEPARATOR)) ==
            DIRECTORY_SEPARATOR) {
            $file = (string) substr($file, 0, -strlen(DIRECTORY_SEPARATOR));
        }

        // Class and method / function match.
        if (isset($this->_loggers["$method:$class:"])) {
            return $this->_loggers["$method:$class:"];
        }

        // Class match.
        if ($class != "" && isset($this->_loggers[":$class:"])) {
            return $this->_loggers[":$class:"];
        }

        // File match.
        $parts = explode(DIRECTORY_SEPARATOR, $file);
        while (count($parts)) {
            $offset = implode(DIRECTORY_SEPARATOR, $parts);
            if ($offset == '') {
                break;
            }

            if (isset($this->_loggers["::$offset"])) {
                return $this->_loggers["::$offset"];
            }
            array_pop($parts);
        }

        // Root logger.
        return $this->_loggers['::'];
    }

    /**
     * Return a flag indicating whether a logger with
     * the given identifier was registered with Plop.
     *
     * \param string|Plop_LoggerInterface $offset
     *      A logger identifier. You may also pass a logger,
     *      in which case, that logger's identifier will be
     *      used for the test.
     *
     * \retval bool
     *      A flag indicating whether a logger was registered
     *      with that identifier (\a TRUE) or not (\a FALSE).
     *
     * \warning
     *      When a logger is passed to this method, it will
     *      only look for a registered logger with the same
     *      identifier. It will not check whether both loggers
     *      are actually the same.
     */
    public function offsetExists($offset)
    {
        if ($offset instanceof Plop_LoggerInterface) {
            $offset = self::_getLoggerId($offset);
        }
        if (!is_string($offset)) {
            throw new Plop_Exception('Invalid identifier');
        }
        return isset($this->_loggers[$offset]);
    }

    /**
     * Unregister a logger.
     *
     * \param string|Plop_LoggerInterface $offset
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
        if ($offset instanceof Plop_LoggerInterface) {
            $offset = self::_getLoggerId($offset);
        }
        if ($offset == "::") {
            throw new Plop_Exception('The root logger cannot be unset');
        }
        unset($this->_loggers[$offset]);
    }

    /// \copydoc Plop_IndirectLoggerAbstract::_getIndirectLogger().
    protected function _getIndirectLogger()
    {
        $caller = self::findCaller();
        return $this["{$caller['func']}:{$caller['class']}:{$caller['fn']}"];
    }

    /**
     * Return information about the caller of this method.
     *
     * \retval array
     *      An associative array with information about the caller.
     *      This array always contains the following keys:
     *      -   "fn" -- the name of the file where the call was made.
     *      -   "lno" -- the line number in that file where the call
     *          was made.
     *      -   "func" -- the name of the function/method where the
     *          call happened.
     *      -   "class" -- the name of the class where the call was
     *          made.
     *
     *      Each of those values may be NULL (or 0 in the case of
     *      "lno") if the information could not be extracted from
     *      the call stack.
     *
     * \note
     *      Here, "caller" means the first context in the call stack
     *      that does not refer to one of Plop's methods/files.
     */
    static public function findCaller()
    {
        if (version_compare(PHP_VERSION, '5.3.6', '>=')) {
            $bt = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        }
        else {
            $bt = debug_backtrace(FALSE);
        }

        $max    = count($bt);
        $pfix1  = dirname(__FILE__) . DIRECTORY_SEPARATOR .
                    'Plop' . DIRECTORY_SEPARATOR;
        $len1   = strlen($pfix1);
        $pfix2  = __FILE__;
        $len2   = strlen($pfix2);
        for ($i = 0; $i < $max; $i++) {
            if (isset($bt[$i]['file']) &&
                strncmp($pfix1, $bt[$i]['file'], $len1) &&
                strncmp($pfix2, $bt[$i]['file'], $len2)) {
                break;
            }

            // Skip frames until we get out of Plop's code.
        }

        if ($i == $max) {
            return array(
                'fn'    => NULL,
                'lno'   => 0,
                'func'  => NULL,
                'class' => NULL,
            );
        }

        return array(
            'fn'    => $bt[$i]['file'],
            'lno'   => $bt[$i]['line'],
            'func'  => (!isset($bt[$i + 1]['function'])
                        ? NULL : $bt[$i + 1]['function']),
            'class' => (!isset($bt[$i + 1]['class'])
                        ? NULL : $bt[$i + 1]['class']),
        );
    }
}

