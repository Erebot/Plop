<?php

class   Plop_Handler_Stream
extends Plop_Handler
{
    protected $stream;

    public function __construct(&$stream = NULL)
    {
        parent::__construct();
        if ($stream === NULL)
            $stream = fopen('php://stderr', 'at');
        $this->stream       =&  $stream;
        $this->formatter    =   NULL;
    }

    public function flush()
    {
        fflush($this->stream);
    }

    public function emit(Plop_Record &$record)
    {
        $msg = $this->format($record);
        fprintf($this->stream, "%s\n", $msg);
        $this->flush();
    }
}

