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

class       Plop_Logger
extends     Plop_LoggerAbstract
{
    protected $_name;
    protected $_level;
    protected $_handlers;
    protected $_emittedWarning;

    public function __construct($name, $level = Plop::NOTSET)
    {
        parent::__construct();
        $this->_name            = $name;
        $this->_level           = $level;
        $this->_handlers        = array();
        $this->_emittedWarning  = FALSE;
    }

    public function getLevel()
    {
        return $this->_level;
    }

    public function setLevel($level)
    {
        if (!is_int($level)) {
            throw new Exception('Not a valid integer');
        }
        $this->_level = $level;
        return $this;
    }

    public function getName()
    {
        return $this->_name;
    }

    public function log($level, $msg, $args = array(), $exception = NULL)
    {
        if ($this->isEnabledFor($level)) {
            $caller = $this->findCaller();
            $record = $this->makeRecord(
                $this->_name,
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
        return $this;
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
                $rv[$k] =& $v;
            }
            unset($v);
        }
        return $rv;
    }

    protected function handle(Plop_RecordInterface $record)
    {
        if ($this->filter($record))
            $this->callHandlers($record);
        return $this;
    }

    public function addHandler(Plop_HandlerInterface $handler)
    {
        if (!in_array($handler, $this->_handlers, TRUE))
            $this->_handlers[] = $handler;
        return $this;
    }

    public function removeHandler(Plop_HandlerInterface $handler)
    {
        $keys = array_keys($this->_handlers, $handler);
        if ($keys[0] !== FALSE) {
            unset($this->_filters[$keys[0]]);
        }
        return $this;
    }

    public function getHandlers()
    {
        return $this->_handlers;
    }

    protected function callHandlers(Plop_RecordInterface $record)
    {
        $found  =   0;

        foreach ($this->_handlers as $handler) {
            $found += 1;
            if ($record['levelno'] >= $handler->getLevel())
                $handler->handle($record);
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
    }

    public function getEffectiveLevel()
    {
        return $this->_level;
    }

    public function isEnabledFor($level)
    {
        $effectiveLevel = $this->getEffectiveLevel();
        return ($level >= $effectiveLevel);
    }
}

