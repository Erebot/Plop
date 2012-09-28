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
 * A logging module, similar to the one provided by Python.
 * It uses the concepts of loggers, handlers & formatters
 * to offer customizable logs.
 */
class       Plop
extends     Plop_IndirectLoggerAbstract
implements  ArrayAccess
{
    const BASIC_FORMAT  = '[%(levelname)s] %(message)s';

    const NOTSET    =  0;
    const DEBUG     = 10;
    const INFO      = 20;
    const WARNING   = 30;
    const WARN      = 30;
    const ERROR     = 40;
    const CRITICAL  = 50;

    static protected $_instance = NULL;
    protected $_loggers;
    protected $_levelNames;
    protected $_created;

    protected function __construct()
    {
        $this->_loggers = array();
        $rootLogger = new Plop_Logger(NULL, NULL, NULL);
        $basicHandler = new Plop_Handler_Stream(STDERR);
        $this[] = $rootLogger->addHandler(
            $basicHandler->setFormatter(
                new Plop_Formatter(self::BASIC_FORMAT)
            )
        );
        $this->_levelNames = array(
            self::NOTSET    => 'NOTSET',
            self::DEBUG     => 'DEBUG',
            self::INFO      => 'INFO',
            self::WARNING   => 'WARNING',
            self::ERROR     => 'ERROR',
            self::CRITICAL  => 'CRITICAL',
        );
        $this->_created = microtime(TRUE);
    }

    public function __clone()
    {
        throw new Exception('cloning this class is forbidden!');
    }

    static public function & getInstance()
    {
        if (self::$_instance === NULL) {
            $c = __CLASS__;
            self::$_instance = new $c();
        }
        return self::$_instance;
    }

    public function getCreationDate()
    {
        return $this->_created;
    }

    public function getLogger($file = NULL, $class = NULL, $method = NULL)
    {
        return $this["$method:$class:$file"];
    }

    public function addLogger(Plop_LoggerInterface $logger)
    {
        $this[] = $logger;
    }

    public function addLevelName($lvl, $lvlName)
    {
        $this->_levelNames[$lvl] = $lvlName;
    }

    public function getLevelName($lvl)
    {
        if (!isset($this->_levelNames[$lvl])) {
            return "Level ".$lvl;
        }
        return $this->_levelNames[$lvl];
    }

    public function getLevelValue($name)
    {
        $key = array_search($name, $this->_levelNames, TRUE);
        return (int) $key; // FALSE is silently converted to 0.
    }

    public function offsetSet($name, $logger)
    {
        if (!($logger instanceof Plop_LoggerInterface)) {
            throw new RuntimeException('Invalid logger');
        }

        if ($name instanceof Plop_LoggerInterface) {
            $name = $name->getId();
        }
        else if (is_string($name)) {
            if ($name != $logger->getId()) {
                throw new RuntimeException('Invalid name');
            }
        }
        else {
            $name = $logger->getId();
        }

        $this->_loggers[$name] = $logger;
    }

    public function offsetGet($name)
    {
        if (!is_string($name)) {
            throw new RuntimeException('Invalid logger name');
        }

        $parts = explode(':', $name, 3);
        if (count($parts) != 3) {
            throw new RuntimeException('Invalid logger name');
        }
        list($method, $class, $file) = $parts;
        if (substr($file, -strlen(DIRECTORY_SEPARATOR)) ==
            DIRECTORY_SEPARATOR) {
            $file = (string) substr($file, 0, -strlen(DIRECTORY_SEPARATOR));
        }

        // File + class + method match.
        if (isset($this->_loggers["$method:$class:$file"])) {
            return $this->_loggers["$method:$class:$file"];
        }

        // File + class match.
        // Note: for functions, this is actually a file match,
        //       which is redundant with the loop afterwards,
        //       but that's okay 'cause the performance penalty
        //       ain't that big.
        if (isset($this->_loggers[":$class:$file"])) {
            return $this->_loggers[":$class:$file"];
        }

        // File match.
        $parts = explode(DIRECTORY_SEPARATOR, $file);
        while ($parts) {
            $name = implode(DIRECTORY_SEPARATOR, $parts);
            if (isset($this->_loggers["::$name"])) {
                return $this->_loggers["::$name"];
            }
            array_pop($parts);
        }

        // Root logger.
        return $this->_loggers['::'];
    }

    public function offsetExists($name)
    {
        if ($name instanceof Plop_LoggerInterface) {
            $name = $name->getId();
        }
        return isset($this->_loggers[$name]);
    }

    public function offsetUnset($name)
    {
        if ($name instanceof Plop_LoggerInterface) {
            $name = $name->getId();
        }
        if ($name == "::") {
            throw new RuntimeException('The root logger cannot be unset!');
        }
        unset($this->_loggers[$name]);
    }

    protected function _getIndirectLogger()
    {
        $caller = self::findCaller();
        return $this["${caller['func']}:${caller['class']}:${caller['fn']}"];
    }

    static public function findCaller()
    {
        if (version_compare(PHP_VERSION, '5.3.6', '>=')) {
            $bt = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        }
        else {
            $bt = debug_backtrace(FALSE);
        }

        $dir    = dirname(__FILE__) . DIRECTORY_SEPARATOR;
        $len    = strlen($dir);
        $max    = count($bt);
        for ($i = 1; $i < $max && !strncmp($dir, $bt[$i]['file'], $len); $i++) {
            ; // Skip frames until we get out of logging code.
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
            'func'  => ($i + 1 == $max ? NULL : $bt[$i + 1]['function']),
            'class' => ($i + 1 == $max ? NULL : $bt[$i + 1]['class']),
        );
    }
}

