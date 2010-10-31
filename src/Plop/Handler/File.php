<?php

namespace PEAR2\Plop\Handler;

class   File
extends Stream
{
    public $baseFilename;
    public $mode;

    public function __construct($filename, $mode='a', $encoding=NULL, $delay=0)
    {
        $this->baseFilename = $filename;
        $this->mode         = $mode;
        $this->encoding     = $encoding;
        if ($delay) {
            Handler::__construct();
            $this->_stream = FALSE;
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
        if ($this->_stream) {
            $this->flush();
            fclose($this->_stream);
            parent::close();
        }
    }
}

