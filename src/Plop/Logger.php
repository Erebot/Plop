<?php

class   Plop_Logger
extends Plop_Filterer
{
    static public $root      = NULL;
    static public $manager   = NULL;

    public $name;
    public $level;
    public $parent;
    public $propagate;
    public $handlers;
    public $disabled;

    public function __construct($name, $level = PLOP_LEVEL_NOTSET)
    {
        parent::__construct();
        $this->name         = $name;
        $this->level        = $level;
        $this->parent       = NULL;
        $this->propagate    = 1;
        $this->handlers     = array();
        $this->disabled     = 0;
    }

    public function setLevel($level)
    {
        $this->level = $level;
    }

    public function debug($msg, $args = array(), $exception = NULL)
    {
        return $this->log(PLOP_LEVEL_DEBUG, $msg, $args, $exception);
    }

    public function info($msg, $args = array(), $exception = NULL)
    {
        return $this->log(PLOP_LEVEL_INFO, $msg, $args, $exception);
    }

    public function warning($msg, $args = array(), $exception = NULL)
    {
        return $this->log(PLOP_LEVEL_WARNING, $msg, $args, $exception);
    }

    public function warn($msg, $args = array(), $exception = NULL)
    {
        return $this->log(PLOP_LEVEL_WARNING, $msg, $args, $exception);
    }

    public function error($msg, $args = array(), $exception = NULL)
    {
        return $this->log(PLOP_LEVEL_ERROR, $msg, $args, $exception);
    }

    public function critical($msg, $args = array(), $exception = NULL)
    {
        return $this->log(PLOP_LEVEL_CRITICAL, $msg, $args, $exception);
    }

    public function fatal($msg, $args = array(), $exception = NULL)
    {
        return $this->log(PLOP_LEVEL_CRITICAL, $msg, $args, $exception);
    }

    public function exception($msg, $exception, $args = array())
    {
        return $this->log(PLOP_LEVEL_ERROR, $msg, $args, $exception);
    }

    public function log($level, $msg, $args = array(), $exception = NULL)
    {
        if ($this->isEnabledFor($level)) {
            $caller = $this->findCaller();
            $record = $this->makeRecord(
                $this->name,
                $level,
                $caller['fn'],
                $caller['lno'],
                $msg,
                $args,
                $exception,
                $caller['func']
            );
            $this->handle($record);
        }
    }

    public function findCaller()
    {
        $bt     = debug_backtrace();
        $dir    = dirname(__FILE__);
        $max    = count($bt);
        for ($i = 1; $i < $max && dirname($bt[$i]['file']) == $dir; $i++)
            ; // Skip frames until we get out of logging code.

        if ($i == $max)
            return array(
                'fn'    => '???',
                'lno'   => 0,
                'func'  => '???',
            );

        return array(
            'fn'    => $bt[$i]['file'],
            'lno'   => $bt[$i]['line'],
            'func'  => ($i+1 == $max ? '???' : $bt[$i+1]['function']),
        );
    }

    public function makeRecord(
        $name,
        $level,
        $fn,
        $lno,
        $msg,
        $args,
        $exception  = NULL,
        $func       = NULL,
        $extra      = NULL
    )
    {
        $rv = new Plop_Record(
            $name,
            $level,
            $fn,
            $lno,
            $msg,
            $args,
            $exception,
            $func
        );

        if ($extra) {
            foreach ($extra as $k => &$v) {
                if (
                    in_array($key, array('message', 'asctime')) ||
                    in_array($key, $rv->dict)
                )
                    throw new Exception(
                        'Attempt to override '.$k.' in record'
                    );
                $rv->dict[$k] =& $v;
            }
            unset($v);
        }
        return $rv;
    }

    public function handle(Plop_Record &$record)
    {
        if (!$this->disabled && $this->filter($record))
            $this->callHandlers($record);
    }

    public function addHandler(Plop_Handler &$handler)
    {
        if (!in_array($handler, $this->handlers, TRUE))
            $this->handlers[] =& $handler;
    }

    public function removeHandler(Plop_Handler &$handler)
    {
        $key = array_search($handler, $this->handlers, TRUE);
        if ($key !== FALSE) {
            $handler->acquire();
            unset($this->filters[$key]);
            $handler->release();
        }
    }

    public function callHandlers(Plop_Record &$record)
    {
        $found  =   0;
        for ($c = $this; $c; $c = $c->parent) {
            foreach ($c->handlers as &$handler) {
                $found += 1;
                if ($record->dict['levelno'] >= $handler->level)
                    $handler->handle($record);
            }
            unset($handler);
            if (!$c->propagate)
                break;
        }
        if (!$found && !self::$manager->emittedNoHandlerWarning) {
            fprintf(
                STDERR,
                'No handlers could be found for logger "%s"'."\n",
                $this->name
            );
            self::$manager->emittedNoHandlerWarning = 1;
        }
    }

    public function getEffectiveLevel()
    {
        for ($logger = $this; $logger; $logger = $logger->parent)
            if ($logger->level)
                return $logger->level;
        return PLOP_LEVEL_NOTSET;
    }

    public function isEnabledFor($level)
    {
        if (self::$manager->disable >= $level)
            return FALSE;
        $effLevel = $this->getEffectiveLevel();
        return ($level >= $effLevel);
    }
}

Plop_Logger::$root     = new Plop_RootLogger(PLOP_LEVEL_WARNING);
Plop_Logger::$manager  = new Plop_Manager(Plop_Logger::$root);

