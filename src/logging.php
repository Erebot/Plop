<?php

/**
 * A logging module, similar to the one provided by Python.
 * It uses the concepts of loggers, handlers & formatters
 * to offer customizable logs.
 */

define('EREBOT_LOG_CRITICAL',   50);
define('EREBOT_LOG_ERROR',      40);
define('EREBOT_LOG_WARNING',    30);
define('EREBOT_LOG_WARN',       EREBOT_LOG_WARNING);
define('EREBOT_LOG_INFO',       20);
define('EREBOT_LOG_DEBUG',      10);
define('EREBOT_LOG_NOTSET',      0);

define('EREBOT_LOG_XMLNS', 'http://www.erebot.net/xmlns/logging');

require_once(dirname(__FILE__).'/manager.php');
require_once(dirname(__FILE__).'/filterer.php');
require_once(dirname(__FILE__).'/logger.php');
require_once(dirname(__FILE__).'/formatter.php');
require_once(dirname(__FILE__).'/handler.php');
require_once(dirname(__FILE__).'/handlers/FileHandler.php');
require_once(dirname(__FILE__).'/filter.php');
require_once(dirname(__FILE__).'/record.php');
require_once(dirname(__FILE__).'/config/configParser.php');

class ErebotLogging
{
    const BASIC_FORMAT  = "%(levelname)s:%(name)s:%(message)s";

    const NOTSET    = EREBOT_LOG_NOTSET;
    const DEBUG     = EREBOT_LOG_DEBUG;
    const INFO      = EREBOT_LOG_INFO;
    const WARNING   = EREBOT_LOG_WARNING;
    const WARN      = EREBOT_LOG_WARNING;
    const ERROR     = EREBOT_LOG_ERROR;
    const CRITICAL  = EREBOT_LOG_CRITICAL;

    static protected $instance = NULL;
    protected $loggers;
    protected $loggerClass;
    protected $levelNames;
    public $created;

    protected function __construct()
    {
        $this->loggerClass  = 'ErebotLogger';
        $this->loggers      = array();
        $this->levelNames   = array(
            EREBOT_LOG_NOTSET   => 'NOTSET',
            EREBOT_LOG_DEBUG    => 'DEBUG',
            EREBOT_LOG_INFO     => 'INFO',
            EREBOT_LOG_WARNING  => 'WARNING',
            EREBOT_LOG_ERROR    => 'ERROR',
            EREBOT_LOG_CRITICAL => 'CRITICAL',
        );
        $this->levelNames   = $this->levelNames +
                                array_flip($this->levelNames);
        $this->created      = microtime(TRUE);
    }

    public function __clone()
    {
        throw new Exception('cloning this class is forbidden!');
    }

    static public function & getInstance()
    {
        if (self::$instance === NULL) {
            $c = __CLASS__;
            self::$instance = new $c();
        }
        return self::$instance;
    }

    public function getLogger($name = NULL)
    {
        if ($name === NULL)
            return ErebotLogger::$root;
        return ErebotLogger::$manager->getLogger($name);
    }

    public function getLoggerClass()
    {
        return $this->loggerClass;
    }

    public function debug($msg, $args = array(), $exc_info = NULL)
    {
        return $this->log(EREBOT_LOG_DEBUG, $msg, $args, $exc_info);
    }

    public function info($msg, $args = array(), $exc_info = NULL)
    {
        return $this->log(EREBOT_LOG_INFO, $msg, $args, $exc_info);
    }

    public function warning($msg, $args = array(), $exc_info = NULL)
    {
        return $this->log(EREBOT_LOG_WARNING, $msg, $args, $exc_info);
    }

    public function warn($msg, $args = array(), $exc_info = NULL)
    {
        return $this->log(EREBOT_LOG_WARNING, $msg, $args, $exc_info);
    }

    public function error($msg, $args = array(), $exc_info = NULL)
    {
        return $this->log(EREBOT_LOG_ERROR, $msg, $args, $exc_info);
    }

    public function critical($msg, $args = array(), $exc_info = NULL)
    {
        return $this->log(EREBOT_LOG_CRITICAL, $msg, $args, $exc_info);
    }

    public function fatal($msg, $args = array(), $exc_info = NULL)
    {
        return $this->log(EREBOT_LOG_CRITICAL, $msg, $args, $exc_info);
    }

    public function exception($msg, $exc_info, $args = array())
    {
        return $this->log(EREBOT_LOG_ERROR, $msg, $args, $exc_info);
    }

    public function log($lvl, $msg, $args = array(), $exc_info = NULL)
    {
        $this->basicConfig();
        $root = $this->getLogger();
        return $root->log($lvl, $msg, $args, $exc_info);
    }

    public function disable($level)
    {
        ErebotLogger::$manager->disable = $level;
    }

    public function addLevelName($lvl, $lvlName)
    {
        $this->levelNames[$lvl] = $lvlName;
        $this->levelNames[$lvlName] = $lvl;
    }

    public function getLevelName($lvl)
    {
        if (!isset($this->levelNames[$lvl]))
            return "Level ".$lvl;

        return $this->levelNames[$lvl];
    }

    public function makeLogRecord($attrs)
    {
        $rv = ErebotLoggingRecord(NULL, NULL, "", 0, "", array(), NULL, NULL);
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
                $hdlr = new ErebotLoggingFileHandler($filename, $mode);
            }
            else {
                $stream = isset($args['stream']) ? $args['stream'] : NULL;
                $hdlr = new ErebotLoggingStreamHandler($stream);
            }
            $fs = isset($args['format']) ? $args['format'] : self::BASIC_FORMAT;
            $dfs = isset($args['datefmt']) ? $args['datefmt'] : NULL;
            $fmt = new ErebotLoggingFormatter($fs, $dfs);
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
        if (!class_exists($class) && strcasecmp($class, 'ErebotLogger') &&
            !is_subclass_of($class, 'ErebotLogger'))
            throw new EErebotInvalidValue($class);

        $this->loggerClass = $class;
    }

    public function fileConfig($fname, $defaults = array(), $source = 'INI')
    {
        require_once(dirname(__FILE__).'/config/'.$source.'.php');
        $class = 'ErebotLoggingConfig'.$source;
        $configParser = new $class($this, $fname);
        $configParser->doWork();
    }
}

?>
