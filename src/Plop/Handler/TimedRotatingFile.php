<?php
/*
    This file is part of Plop.

    Plop is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    Plop is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with Plop.  If not, see <http://www.gnu.org/licenses/>.
*/

class   Plop_Handler_TimedRotatingFile
extends Plop_Handler_RotatingAbstract
{
    protected $_when;
    protected $_backupCount;
    protected $_utc;
    protected $_interval;
    protected $_suffix;
    protected $_extMatch;
    protected $_dayOfWeek;
    protected $_rolloverAt;

    static protected $_dayNames = array(
        'Monday',
        'Tuesday',
        'Wednesday',
        'Thursday',
        'Friday',
        'Saturday',
        'Sunday'
    );

    public function __construct(
        $filename,
        $when           = 'h',
        $interval       = 1,
        $backupCount    = 0,
        $encoding       = NULL,
        $delay          = 0,
        $utc            = 0
    )
    {
        parent::__construct($filename, 'a', $encoding, $delay);
        $this->_when        = strtoupper($when);
        $this->_backupCount = $backupCount;
        $this->_utc         = $utc;
        $this->_rolloverAt  = NULL;
        $this->_dayOfWeek   = NULL;

        if ($this->_when == 'S') {
            $this->_interval    = 1;
            $this->_suffix      = '%Y-%m-%d_%H-%M-%S';
            $this->_extMatch    = '^\\d{4}-\\d{2}-\\d{2}_\\d{2}-\\d{2}-\\d{2}$';
        }
        else if ($this->_when == 'M') {
            $this->_interval    = 60;
            $this->_suffix      = '%Y-%m-%d_%H-%M';
            $this->_extMatch    = '^\\d{4}-\\d{2}-\\d{2}_\\d{2}-\\d{2}$';
        }
        else if ($this->_when == 'H') {
            $this->_interval    = 60 * 60;
            $this->_suffix      = '%Y-%m-%d_%H';
            $this->_extMatch    = '^\\d{4}-\\d{2}-\\d{2}_\\d{2}$';
        }
        else if ($this->_when == 'D' || $this->_when == 'MIDNIGHT') {
            $this->_interval    = 60 * 60 * 24;
            $this->_suffix      = '%Y-%m-%d';
            $this->_extMatch    = '^\\d{4}-\\d{2}-\\d{2}$';
        }
        else if (substr($this->_when, 0, 1) == 'W') {
            $this->_interval = 60 * 60 * 24 * 7;
            if (strlen($this->_when) != 2)
                throw new Exception(
                    sprintf(
                        'You must specify a day for weekly rollover '.
                        'from 0 to 6 (0 is Monday): %s',
                        $this->_when
                    )
                );
            $ord = ord($this->_when[1]);
            if ($ord < ord('0') || $ord > ord('6'))
                throw new Exception(
                    sprintf(
                        'Invalid day specified for weekly rollover: %s',
                        $this->_when
                    )
                );
            $this->_dayOfWeek = (int) $this->_when[1];
            $this->_suffix = '%Y-%m-%d';
            $this->_extMatch = '^\\d{4}-\\d{2}-\\d{2}$';
        }
        else
            throw new Exception(
                sprintf(
                    'Invalid rollover interval specified: %s',
                    $this->_when
                )
            );
        $this->_interval    = $this->_interval * $interval;
        $this->_rolloverAt  = $this->_computeRollover(time());
    }

    protected function _computeRollover($currentTime)
    {
        if ($this->_when == 'MIDNIGHT')
            return strtotime("midnight + 1 day", $currentTime);
        if (substr($this->_when, 0, 1) == 'W')
            return strtotime(
                "next " . self::$_dayNames[$this->_dayOfWeek],
                $currentTime
            );
        return $currentTime + $this->_interval;
    }

    protected function _shouldRollover(Plop_RecordInterface $record)
    {
        $t = time();
        if ($t >= $this->_rolloverAt)
            return TRUE;
        return FALSE;
    }

    public function getFilesToDelete()
    {
        $dirName    = dirname($this->_baseFilename);
        $baseName   = basename($this->_baseFilename);
        $fileNames  = scandir($dirName);
        $result     = array();
        $prefix     = $baseName . '.';
        $plen       = strlen($prefix);
        foreach ($fileNames as $fileName) {
            if ($fileName == '.' || $fileName == '..')
                continue;
            if (!strncmp($fileName, $prefix, $plen)) {
                $suffix = substr($fileName, $plen);
                if (preg_match($this->_extMatch, $suffix))
                    $result[] = $dirName.DIRECTORY_SEPARATOR.$fileName;
            }
        }
        sort($result);
        $rlen = count($result);
        if ($rlen < $this->_backupCount)
            $result = array();
        else
            $result = array_slice($result, 0, $rlen - $this->_backupCount);
        return $result;
    }

    public function doRollover()
    {
        if ($this->_stream)
            fclose($this->_stream);
        $t      = $this->_rolloverAt - $this->_interval;
        if ($this->_utc)
            $formatFunc = 'gmstrftime';
        else
            $formatFunc = 'strftime';
        $dfn    = $this->_baseFilename.'.'.$formatFunc($this->_suffix, $t);
        if (file_exists($dfn))
            @unlink($dfn);
        rename($this->_baseFilename, $dfn);
        if ($this->_backupCount > 0) {
            foreach ($this->_getFilesToDelete() as $s)
                @unlink($s);
        }
        $this->_mode    = 'w';
        $this->_stream  = $this->_open();
        $currentTime    = time();
        $newRolloverAt  = $this->_computeRollover($currentTime);
        while ($newRolloverAt <= $currentTime)
            $newRolloverAt += $this->_interval;
        $this->_rolloverAt = $newRolloverAt;
    }
}

