<?php
/*
    This file is part of Plop.

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

namespace PEAR2\Plop;

/**
 * A logging module, similar to the one provided by Python.
 * It uses the concepts of loggers, handlers & formatters
 * to offer customizable logs.
 */
class Plop
{
    const BASIC_FORMAT  = "%(levelname)s:%(name)s:%(message)s";

    const NOTSET    = 0;
    const DEBUG     = 10;
    const INFO      = 20;
    const WARNING   = 30;
    const WARN      = 30;
    const ERROR     = 40;
    const CRITICAL  = 50;

    static protected $_instance = NULL;
    protected $_loggers;
    protected $_loggerClass;
    protected $_levelNames;
    public $created;

    protected function __construct()
    {
        $this->_loggerClass  = '\\PEAR2\\Plop\\Logger';
        $this->_loggers      = array();
        $this->_levelNames   = array(
            self::NOTSET    => 'NOTSET',
            self::DEBUG     => 'DEBUG',
            self::INFO      => 'INFO',
            self::WARNING   => 'WARNING',
            self::ERROR     => 'ERROR',
            self::CRITICAL  => 'CRITICAL',
        );
        $this->_levelNames   = $this->_levelNames +
                                array_flip($this->_levelNames);
        $this->created      = microtime(TRUE);
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

    public function getLogger($name = NULL)
    {
        if ($name === NULL)
            return Logger::$root;
        return Logger::$manager->getLogger($name);
    }

    public function getLoggerClass()
    {
        return $this->_loggerClass;
    }

    public function debug($msg, $args = array(), $exception = NULL)
    {
        return $this->log(self::DEBUG, $msg, $args, $exception);
    }

    public function info($msg, $args = array(), $exception = NULL)
    {
        return $this->log(self::INFO, $msg, $args, $exception);
    }

    public function warning($msg, $args = array(), $exception = NULL)
    {
        return $this->log(self::WARNING, $msg, $args, $exception);
    }

    public function warn($msg, $args = array(), $exception = NULL)
    {
        return $this->log(self::WARNING, $msg, $args, $exception);
    }

    public function error($msg, $args = array(), $exception = NULL)
    {
        return $this->log(self::ERROR, $msg, $args, $exception);
    }

    public function critical($msg, $args = array(), $exception = NULL)
    {
        return $this->log(self::CRITICAL, $msg, $args, $exception);
    }

    public function fatal($msg, $args = array(), $exception = NULL)
    {
        return $this->log(self::CRITICAL, $msg, $args, $exception);
    }

    public function exception($msg, $exception, $args = array())
    {
        return $this->log(self::ERROR, $msg, $args, $exception);
    }

    public function log($lvl, $msg, $args = array(), $exception = NULL)
    {
        $this->basicConfig();
        $root = $this->getLogger();
        return $root->log($lvl, $msg, $args, $exception);
    }

    public function disable($level)
    {
        Logger::$manager->disable = $level;
    }

    public function addLevelName($lvl, $lvlName)
    {
        $this->_levelNames[$lvl] = $lvlName;
        $this->_levelNames[$lvlName] = $lvl;
    }

    public function getLevelName($lvl)
    {
        if (!isset($this->_levelNames[$lvl]))
            return "Level ".$lvl;

        return $this->_levelNames[$lvl];
    }

    public function makeLogRecord($attrs)
    {
        $rv = Record(NULL, NULL, "", 0, "", array(), NULL, NULL);
        $rv->dict = array_merge($rv->dict, $attrs);
        return $rv;
    }

    public function basicConfig($args = array())
    {
        $root = $this->getLogger();
        if (!count($root->handlers)) {
            $filename = isset($args['filename']) ? $args['filename'] : NULL;
            if ($filename !== NULL) {
                $mode = isset($args['filemode']) ? $args['filemode'] : 'a';
                $hdlr = new Handler\File($filename, $mode);
            }
            else {
                $stream = isset($args['stream']) ? $args['stream'] : NULL;
                $hdlr = new Handler\Stream($stream);
            }
            $fs = isset($args['format']) ? $args['format'] : self::BASIC_FORMAT;
            $dfs = isset($args['datefmt']) ? $args['datefmt'] : NULL;
            $fmt = new Formatter($fs, $dfs);
            $hdlr->setFormatter($fmt);
            $root->addHandler($hdlr);
            if (isset($args['level'])) {
                $level = $args['level'];
                if (is_numeric($level))
                    $level = (int) $level;
                else
                    $level = $this->getLevelName($level);
                $root->setLevel($level);
            }
        }
    }

    public function shutdown()
    {
        /// @TODO
    }

    public function setLoggerClass($class)
    {
        if (!class_exists($class) ||
            !is_subclass_of($class, '\\PEAR2\\Plop\\Logger'))
            throw new Exception($class);

        $this->_loggerClass = $class;
    }

    public function fileConfig(
        $fname,
        $defaults   = array(),
        $class     = '\\PEAR2\\Plop\\Config\\Format\\INI'
    )
    {
        $configParser = new $class($this, $fname);
        $configParser->doWork();
    }
}

