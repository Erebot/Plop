<?php

class   Plop_Handler_Stream
extends Plop_Handler
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

    public function emit(Plop_Record &$record)
    {
        $msg = $this->format($record);
        fprintf($this->_stream, "%s\n", $msg);
        $this->flush();
    }
}

