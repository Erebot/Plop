<?php

include_once(dirname(__FILE__).'/StreamHandler.php');

class   ErebotLoggingFileHandler
extends ErebotLoggingStreamHandler
{
    public $baseFilename;
    public $mode;

    public function __construct($filename, $mode='a', $encoding=NULL, $delay=0)
    {
        $this->baseFilename = $filename;
        $this->mode         = $mode;
        $this->encoding     = $encoding;
        if ($delay) {
            ErebotLoggingHandler::__construct();
            $this->stream = FALSE;
        }
        else
            parent::__construct($this->open());
    }

    protected function open()
    {
        $stream = fopen($this->baseFilename, $this->mode);
        if (function_exists('stream_encoding') &&
            $this->encoding !== NULL && $stream !== FALSE)
            stream_encoding($stream, $this->encoding);
        return $stream;
    }

    public function close()
    {
        if ($this->stream) {
            $this->flush();
            fclose($this->stream);
            parent::close();
        }
    }
}

?>
