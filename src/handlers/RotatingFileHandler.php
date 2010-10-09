<?php

include_once(dirname(__FILE__).'/BaseRotatingHandler.php');

class   ErebotLoggingRotatingFileHandler
extends ErebotLoggingBaseRotatingHandler
{
    public $maxBytes;
    public $backupCount;

    public function __construct($filename, $mode='a', $maxBytes=0, $backupCount=0, $encoding=NULL, $delay=0)
    {
        if ($maxBytes > 0)
            $mode = 'a';
        parent::__construct($filename, $mode, $encoding, $delay);
        $this->maxBytes     = $maxBytes;
        $this->backupCount  = $backupCount;
    }

    public function doRollover()
    {
        fclose($this->stream);
        if ($this->backupCount > 0) {
            for ($i = $this->backupCount - 1; $i > 0; $i--) {
                $sfn = sprintf("%s.%d", $this->baseFilename, $i);
                $dfn = sprintf("%s.%d", $this->baseFilename, $i + 1);
                if (file_exists($sfn)) {
                    if (file_exists($dfn))
                        @unlink($dfn);
                    rename($sfn, $dfn);
                }
            }
            $dfn = sprintf("%s.1", $this->baseFilename);
            if (file_exists($dfn))
                @unlink($dfn);
            rename($this->baseFilename, $dfn);
        }
        $this->mode     = 'w';
        $this->stream   = $this->_open();
    }

    public function shouldRollover(ErebotLoggingRecord &$record)
    {
        if (!$this->stream)
            $this->stream = $this->open();
        if ($this->maxBytes > 0) {
            $msg = $this->format($record)."\n";
            // The python doc states this is due to a non-POSIX-compliant
            // behaviour under Windows.
            fseek($this->stream, 0, SEEK_END);
            if (ftell($this->stream) + strlen($msg) >= $this->maxBytes)
                return TRUE;
        }
        return FALSE;
    }
}

?>
