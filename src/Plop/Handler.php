<?php

class   Plop_Handler
extends Plop_Filterer
{
    static public $defaultFormatter = NULL;
    public $level;
    public $formatter;
    public $lock;

    public function __construct($level = PLOP_LEVEL_NOTSET)
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
        throw new Exception(
            'Emit must be implemented by Plop_Handler subclasses');
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

    public function handleError(Plop_Record &$record, Exception &$exc_info)
    {
        fprintf(STDERR, "%s", $exc_info);
    }
}

Plop_Handler::$defaultFormatter = new Plop_Formatter();

