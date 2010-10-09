<?php

include_once(dirname(__FILE__).'/FileHandler.php');

class   ErebotLoggingWatchedFileHandler
extends ErebotLoggingFileHandler
{
    protected $dev;
    protected $ino;

    public function __construct($filename, $mode='a', $encoding=NULL, $delay=0)
    {
        parent::__construct($filename, $mode, $encoding, $delay);
        if (!file_exists($filename)) {
            $this->dev = $this->ino = -1;
        }
        else {
            $stats = stat($filename);
            $this->dev = $stats['dev'];
            $this->ino = $stats['ino'];
        }
    }

    public function emit(ErebotLoggingRecord &$record)
    {
        if (!file_exists($this->baseFilename)) {
            $stats = NULL;
            $changed = 1;
        }
        else {
            $stats = stat($this->baseFilename);
            $changed = (($stat['dev'] != $this->dev) ||
                        ($stat['ino'] != $this->ino));
        }
        if ($changed && $this->stream !== FALSE) {
            fflush($this->stream);
            fclose($this->stream);
            $this->open();
            if (!$stats)
                $stats = stat($this->baseFilename);
            $this->dev = $stats['dev'];
            $this->ino = $stats['ino'];
        }
        parent::emit($record);
    }
}

?>
