<?php

namespace PEAR2\Plop\Handler;
use \PEAR2\Plop\Handler,
    \PEAR2\Plop\Record;

class   Stream
extends Handler
{
    protected $_stream;

    public function __construct(&$stream = NULL)
    {
        parent::__construct();
        if ($stream === NULL)
            $stream = fopen('php://stderr', 'at');
        $this->_stream       =&  $stream;
        $this->formatter    =   NULL;
    }

    public function flush()
    {
        fflush($this->_stream);
    }

    public function emit(Record &$record)
    {
        $msg = $this->format($record);
        fprintf($this->_stream, "%s\n", $msg);
        $this->flush();
    }
}

