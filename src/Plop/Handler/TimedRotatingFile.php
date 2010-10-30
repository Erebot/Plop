<?php

class   Plop_Handler_TimedRotatingFile
extends Plop_Handler_BaseRotating
{
    public $when;
    public $backupCount;
    public $utc;
    public $interval;
    public $suffix;
    public $extMatch;
    public $dayOfWeek;
    public $rolloverAt;

    static protected $dayNames = array( 'Monday',       'Tuesday',
                                        'Wednesday',    'Thursday',
                                        'Friday',       'Saturday',
                                        'Sunday');

    public function __construct($filename, $when='h', $interval=1, $backupCount=0, $encoding=NULL, $delay=0, $utc=0)
    {
        parent::__construct($filename, 'a', $encoding, $delay);
        $this->when         = strtoupper($when);
        $this->backupCount  = $backupCount;
        $this->utc          = $utc;
        $currentTime        = time();
        $this->rolloverAt   = NULL;
        $this->dayOfWeek    = NULL;

        if ($this->when == 'S') {
            $this->interval = 1;
            $this->suffix   = '%Y-%m-%d_%H-%M-%S';
            $this->extMatch = '^\\d{4}-\\d{2}-\\d{2}_\\d{2}-\\d{2}-\\d{2}$';
        }
        else if ($this->when == 'M') {
            $this->interval = 60;
            $this->suffix   = '%Y-%m-%d_%H-%M';
            $this->extMatch = '^\\d{4}-\\d{2}-\\d{2}_\\d{2}-\\d{2}$';
        }
        else if ($this->when == 'H') {
            $this->interval = 60 * 60;
            $this->suffix   = '%Y-%m-%d_%H';
            $this->extMatch = '^\\d{4}-\\d{2}-\\d{2}_\\d{2}$';
        }
        else if ($this->when == 'D' || $this->when == 'MIDNIGHT') {
            $this->interval = 60 * 60 * 24;
            $this->suffix   = '%Y-%m-%d';
            $this->extMatch = '^\\d{4}-\\d{2}-\\d{2}$';
        }
        else if (substr($this->when, 0, 1) == 'W') {
            $this->interval = 60 * 60 * 24 * 7;
            if (strlen($this->when) != 2)
                throw new Exception(sprintf('You must specify a day for '.
                    'weekly rollover from 0 to 6 (0 is Monday): %s',
                    $this->when));
            $ord = ord($this->when[1]);
            if ($ord < ord('0') || $ord > ord('6'))
                throw new Exception(sprintf('Invalid day specified for '.
                    'weekly rollover: %s', $this->when));
            $this->dayOfWeek = (int) $this->when[1];
            $this->suffix = '%Y-%m-%d';
            $this->extMatch = '^\\d{4}-\\d{2}-\\d{2}$';
        }
        else
            throw new Exception(sprintf('Invalid rollover interval '.
                                'specified: %s', $this->when));
        $this->interval     = $this->interval * $interval;
        $this->rolloverAt   = $this->compuleRollover(time());
    }

    public function compuleRollover($currentTime)
    {
        if ($this->when == 'MIDNIGHT')
            return strtotime("midnight + 1 day", $currentTime);
        if (substr($this->when, 0, 1) == 'W')
            return strtotime("next ".self::$dayNames[$this->dayOfWeek], $currentTime);
        return $currentTime + $this->interval;
    }

    public function shouldRollover(Plop_Record &$record)
    {
        $t = time();
        if ($t >= $this->rolloverAt)
            return TRUE;
        return FALSE;
    }

    public function getFilesToDelete()
    {
        $dirName    = dirname($this->baseFilename);
        $baseName   = basename($this->baseFilename);
        $fileNames  = scandir($dirName);
        $result     = array();
        $prefix     = $baseName . '.';
        $plen       = strlen($prefix);
        foreach ($fileNames as $fileName) {
            if ($fileName == '.' || $fileName == '..')
                continue;
            if (!strncmp($fileName, $prefix, $plen)) {
                $suffix = substr($fileName, $plen);
                if (preg_match($this->extMatch, $suffix))
                    $result[] = $dirName.DIRECTORY_SEPARATOR.$fileName;
            }
        }
        sort($result);
        $rlen = count($result);
        if ($rlen < $this->backupCount)
            $result = array();
        else
            $result = array_slice($result, 0, $rlen - $this->backupCount);
        return $result;
    }

    public function doRollover()
    {
        if ($this->stream)
            fclose($this->stream);
        $t      = $this->rolloverAt - $this->interval;
        if ($this->utc)
            $formatFunc = 'gmstrftime';
        else
            $formatFunc = 'strftime';
        $dfn    = $this->baseFilename.'.'.$formatFunc($this->suffix, $t);
        if (file_exists($dfn))
            @unlink($dfn);
        rename($this->baseFilename, $dfn);
        if ($this->backupCount > 0) {
            foreach ($this->getFilesToDelete() as $s)
                @unlink($s);
        }
        $this->mode     = 'w';
        $this->stream   = $this->open();
        $currentTime    = time();
        $newRolloverAt  = $this->compuleRollover($currentTime);
        while ($newRolloverAt <= $currentTime)
            $newRolloverAt += $this->interval;
        $this->rolloverAt = $newRolloverAt;
    }
}

