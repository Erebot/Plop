<?php

namespace PEAR2\Plop\Handler;
use \PEAR2\Plop\Record;

class   WatchedFile
extends File
{
    protected $_dev;
    protected $_ino;

    public function __construct(
        $filename,
        $mode       = 'a',
        $encoding   = NULL,
        $delay      = 0
    )
    {
        parent::__construct($filename, $mode, $encoding, $delay);
        if (!file_exists($filename)) {
            $this->_dev = $this->_ino = -1;
        }
        else {
            $stats = stat($filename);
            $this->_dev = $stats['dev'];
            $this->_ino = $stats['ino'];
        }
    }

    public function emit(Record &$record)
    {
        if (!file_exists($this->baseFilename)) {
            $stats = NULL;
            $changed = 1;
        }
        else {
            $stats = stat($this->baseFilename);
            $changed = (
                ($stat['dev'] != $this->_dev) ||
                ($stat['ino'] != $this->_ino)
            );
        }
        if ($changed && $this->_stream !== FALSE) {
            fflush($this->_stream);
            fclose($this->_stream);
            $this->open();
            if (!$stats)
                $stats = stat($this->baseFilename);
            $this->_dev = $stats['dev'];
            $this->_ino = $stats['ino'];
        }
        parent::emit($record);
    }
}

