<?php

class   PlopStreamHandler
extends PlopHandler
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

    public function emit(PlopRecord &$record)
    {
        $msg = $this->format($record);
        fprintf($this->stream, "%s\n", $msg);
        $this->flush();
    }
}

?>
