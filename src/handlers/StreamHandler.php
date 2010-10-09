<?php

class   ErebotLoggingStreamHandler
extends ErebotLoggingHandler
{
    protected $stream;

    public function __construct(&$stream = NULL)
    {
        parent::__construct();
        if ($stream === NULL)
            $stream = STDERR;
        $this->stream       =&  $stream;
        $this->formatter    =   NULL;
    }

    public function flush()
    {
        fflush($this->stream);
    }

    public function emit(ErebotLoggingRecord &$record)
    {
        $msg = $this->format($record);
        fprintf($this->stream, "%s\n", $msg);
        $this->flush();
    }
}

?>
