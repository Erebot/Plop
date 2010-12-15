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

class   Plop_Handler
extends Plop_Filterer
{
    static public $defaultFormatter = NULL;
    public $level;
    public $formatter;
    public $lock;

    public function __construct($level = Plop::NOTSET)
    {
        parent::__construct();
        $this->level        = $level;
        $this->formatter    = NULL;
        $this->createLock();
    }

    public function createLock()
    {
        $this->lock = NULL;
    }

    public function acquire()
    {
        if ($this->lock)
            $this->lock->acquire();
    }

    public function release()
    {
        if ($this->lock)
            $this->lock->release();
    }

    public function setLevel($level)
    {
        $this->level = $level;
    }

    public function format(Plop_Record &$record)
    {
        if ($this->formatter)
            return $this->formatter->format($record);
        $formatter =& self::$defaultFormatter;
        return $formatter->format($record);
    }

    public function emit(Plop_Record &$record)
    {
        throw new Exception('Emit must be implemented by subclasses');
    }

    public function handle(Plop_Record &$record)
    {
        $rv = $this->format($record);
        if ($rv) {
            $this->acquire();
            try {
                $this->emit($record);
            }
            catch (Exception $e) {
                $this->release();
                throw $e;
            }
            $this->release();
        }
        return $rv;
    }

    public function setFormatter(Plop_Formatter &$fmt)
    {
        $this->formatter =& $fmt;
    }

    public function flush()
    {
    }

    public function close()
    {
        /// @TODO
    }

    public function handleError(Plop_Record &$record, Exception &$exception)
    {
        $stderr = fopen('php://stderr', 'at');
        fprintf($stderr, "%s\n", $exception);
        fclose($stderr);
    }
}

Plop_Handler::$defaultFormatter = new Plop_Formatter();

