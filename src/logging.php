<?php

/**
 * A logging module, similar to the one provided by Python.
 * It uses the concepts of loggers, handlers & formatters
 * to offer customizable logs.
 */

// Prevent multiple inclusions.
if (defined('PLOP_LEVEL_CRITICAL'))
    return;

define('PLOP_LEVEL_CRITICAL',   50);
define('PLOP_LEVEL_ERROR',      40);
define('PLOP_LEVEL_WARNING',    30);
define('PLOP_LEVEL_WARN',       PLOP_LEVEL_WARNING);
define('PLOP_LEVEL_INFO',       20);
define('PLOP_LEVEL_DEBUG',      10);
define('PLOP_LEVEL_NOTSET',     0);

require_once(dirname(__FILE__).'/manager.php');
require_once(dirname(__FILE__).'/filterer.php');
require_once(dirname(__FILE__).'/logger.php');
require_once(dirname(__FILE__).'/formatter.php');
require_once(dirname(__FILE__).'/handler.php');
require_once(dirname(__FILE__).'/handlers/FileHandler.php');
require_once(dirname(__FILE__).'/filter.php');
require_once(dirname(__FILE__).'/record.php');
require_once(dirname(__FILE__).'/config/configParser.php');

class Plop
{
    const BASIC_FORMAT  = "%(levelname)s:%(name)s:%(message)s";

    const NOTSET    = PLOP_LEVEL_NOTSET;
    const DEBUG     = PLOP_LEVEL_DEBUG;
    const INFO      = PLOP_LEVEL_INFO;
    const WARNING   = PLOP_LEVEL_WARNING;
    const WARN      = PLOP_LEVEL_WARNING;
    const ERROR     = PLOP_LEVEL_ERROR;
    const CRITICAL  = PLOP_LEVEL_CRITICAL;

    static protected $instance = NULL;
    protected $loggers;
    protected $loggerClass;
    protected $levelNames;
    public $created;

    protected function __construct()
    {
        $this->loggerClass  = 'PlopLogger';
        $this->loggers      = array();
        $this->levelNames   = array(
            PLOP_LEVEL_NOTSET   => 'NOTSET',
            PLOP_LEVEL_DEBUG    => 'DEBUG',
            PLOP_LEVEL_INFO     => 'INFO',
            PLOP_LEVEL_WARNING  => 'WARNING',
            PLOP_LEVEL_ERROR    => 'ERROR',
            PLOP_LEVEL_CRITICAL => 'CRITICAL',
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
            return PlopLogger::$root;
        return PlopLogger::$manager->getLogger($name);
    }

    public function getLoggerClass()
    {
        return $this->loggerClass;
    }

    public function debug($msg, $args = array(), $exc_info = NULL)
    {
        return $this->log(PLOP_LEVEL_DEBUG, $msg, $args, $exc_info);
    }

    public function info($msg, $args = array(), $exc_info = NULL)
    {
        return $this->log(PLOP_LEVEL_INFO, $msg, $args, $exc_info);
    }

    public function warning($msg, $args = array(), $exc_info = NULL)
    {
        return $this->log(PLOP_LEVEL_WARNING, $msg, $args, $exc_info);
    }

    public function warn($msg, $args = array(), $exc_info = NULL)
    {
        return $this->log(PLOP_LEVEL_WARNING, $msg, $args, $exc_info);
    }

    public function error($msg, $args = array(), $exc_info = NULL)
    {
        return $this->log(PLOP_LEVEL_ERROR, $msg, $args, $exc_info);
    }

    public function critical($msg, $args = array(), $exc_info = NULL)
    {
        return $this->log(PLOP_LEVEL_CRITICAL, $msg, $args, $exc_info);
    }

    public function fatal($msg, $args = array(), $exc_info = NULL)
    {
        return $this->log(PLOP_LEVEL_CRITICAL, $msg, $args, $exc_info);
    }

    public function exception($msg, $exc_info, $args = array())
    {
        return $this->log(PLOP_LEVEL_ERROR, $msg, $args, $exc_info);
    }

    public function log($lvl, $msg, $args = array(), $exc_info = NULL)
    {
        $this->basicConfig();
        $root = $this->getLogger();
        return $root->log($lvl, $msg, $args, $exc_info);
    }

    public function disable($level)
    {
        PlopLogger::$manager->disable = $level;
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
        $rv = PlopRecord(NULL, NULL, "", 0, "", array(), NULL, NULL);
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
                $hdlr = new PlopFileHandler($filename, $mode);
            }
            else {
                $stream = isset($args['stream']) ? $args['stream'] : NULL;
                $hdlr = new PlopStreamHandler($stream);
            }
            $fs = isset($args['format']) ? $args['format'] : self::BASIC_FORMAT;
            $dfs = isset($args['datefmt']) ? $args['datefmt'] : NULL;
            $fmt = new PlopFormatter($fs, $dfs);
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
            !is_subclass_of($class, 'PlopLogger'))
            throw new Exception($class);

        $this->loggerClass = $class;
    }

    public function fileConfig($fname, $defaults = array(), $source = 'INI')
    {
        require_once(dirname(__FILE__).'/config/'.$source.'.php');
        $class = 'PlopConfig'.$source;
        $configParser = new $class($this, $fname);
        $configParser->doWork();
    }
}

?>
