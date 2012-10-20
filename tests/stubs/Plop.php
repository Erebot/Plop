<?php

class   Plop_Stub
extends Plop
{
    public function __construct($empty)
    {
        parent::__construct();
        $this->_created = 12345678.9;
        if ($empty) {
            $this->_loggers = array();
        }
    }

    static public function resetInstanceStub()
    {
        self::$_instance = NULL;
    }

    static public function getLoggerIdStub(Plop_LoggerInterface $logger)
    {
        return self::_getLoggerId($logger);
    }

    public function getIndirectLoggerStub()
    {
        return $this->_getIndirectLogger();
    }
}

