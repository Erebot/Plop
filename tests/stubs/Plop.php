<?php

namespace Plop\Stub;

class Plop extends \Plop\Plop
{
    public function __construct($empty)
    {
        parent::__construct();
        $this->created = 12345678.9;
        if ($empty) {
            $this->loggers = array();
        }
    }

    public static function resetInstanceStub()
    {
        self::$instance = null;
    }

    public static function getLoggerIdStub(\Plop\LoggerInterface $logger)
    {
        return self::getLoggerId($logger);
    }

    public function getIndirectLoggerStub()
    {
        return $this->getIndirectLogger();
    }
}
