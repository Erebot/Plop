<?php

class   ErebotLoggingHandler
extends ErebotLoggingFilterer
{
    static public $defaultFormatter = NULL;
    public $level;
    public $formatter;
    public $lock;

    public function __construct($level = EREBOT_LOG_NOTSET)
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

    public function format(ErebotLoggingRecord &$record)
    {
        if ($this->formatter)
            return $this->formatter->format($record);
        $formatter =& self::$defaultFormatter;
        return $formatter->format($record);
    }

    public function emit(ErebotLoggingRecord &$record)
    {
        throw new EErebotNotImplemented(
            'Emit must be implemented by ErebotLoggingHandler subclasses');
    }

    public function handle(ErebotLoggingRecord &$record)
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

    public function setFormatter(ErebotLoggingFormatter &$fmt)
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

    public function handleError(ErebotLoggingRecord &$record, Exception &$exc_info)
    {
        fprintf(STDERR, "%s", $exc_info);
    }
}

ErebotLoggingHandler::$defaultFormatter = new ErebotLoggingFormatter();

?>
