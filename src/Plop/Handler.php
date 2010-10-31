<?php

namespace PEAR2\Plop;
use PEAR2\Plop\Plop;

class   Handler
extends Filterer
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

    public function format(Record &$record)
    {
        if ($this->formatter)
            return $this->formatter->format($record);
        $formatter =& self::$defaultFormatter;
        return $formatter->format($record);
    }

    public function emit(Record &$record)
    {
        throw new Exception('Emit must be implemented by subclasses');
    }

    public function handle(Record &$record)
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

    public function setFormatter(Formatter &$fmt)
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

    public function handleError(Record &$record, \Exception &$exception)
    {
        fprintf(STDERR, "%s", $exception);
    }
}

Handler::$defaultFormatter = new Formatter();

