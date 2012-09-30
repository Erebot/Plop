<?php
/*
    This file is part of Plop, a simple logging library for PHP.

    Copyright © 2010-2012 François Poirotte

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

/**
 *  \brief
 *      An handler that writes logs to a file
 *      but also handles that file's rotation,
 *      based on time.
 */
class   Plop_Handler_TimedRotatingFile
extends Plop_Handler_RotatingAbstract
{
    /// Log rotation specification (eg. 'W' or 'MIDNIGHT').
    protected $_when;

    /// Number of backup log files to keep.
    protected $_backupCount;

    /// Whether the files are named based on UTC time or local time.
    protected $_utc;

    /// Interval between file rotations.
    protected $_interval;

    /// The date format that will be used as a suffix for the log files.
    protected $_suffix;

    /// A PCRE pattern that matches rotated files.
    protected $_extMatch;

    /// Day of week (0=Monday...6=Sunday) when the log rotation happens.
    protected $_dayOfWeek;

    /// UNIX timestamp of the next log rotation.
    protected $_rolloverAt;

    /// The names of days in English, starting with Monday.
    static protected $_dayNames = array(
        'Monday',
        'Tuesday',
        'Wednesday',
        'Thursday',
        'Friday',
        'Saturday',
        'Sunday'
    );

    /**
     * Construct a new instance of this handler.
     *
     * \param string $filename
     *      Name of the log file to write to.
     *
     * \param string $when
     *      (optional) A log rotation specification,
     *      which acts as a multiplier for the \a $interval
     *      parameter. The default value is "h".
     *      Valid (case-insensitive) values include
     *      -   "s" -- rotate the logs every \a $interval
     *          seconds.
     *      -   "m" -- rotate the logs every \a $interval
     *          minutes.
     *      -   "h" -- rotate the logs every \a $interval
     *          hours.
     *      -   "d" -- rotate the logs every \a $interval
     *          days.
     *      -   "w0" through "w6" -- rotate the logs every
     *          \a $interval weeks. The number after the "w"
     *          character indicates on what day of the week
     *          the log rotation will happen (0 means Monday,
     *          1 means Tuesday and so on).
     *
     * \param int $interval
     *      (optional)-The interval at which log rotations happen.
     *      See also the documentation for the \a $when
     *      parameter for more information on how the two
     *      parameters interact with each other.
     *      The default for both this parameter and \a $when
     *      means that the log rotation will take place every hour.
     *
     * \param int $backupCount
     *      (optional) Specifies how many backup logs are kept
     *      alongside the current log file.
     *      Backup logs are named after the date and time
     *      at which they were created. The exact format
     *      depends on the value of the \a $when parameter.
     *      The default value is 0, which disables deletion
     *      of old backups.
     *
     * \param NULL|string $encoding
     *      (optional) Encoding to use when writing
     *      to the file. Defaults to \a NULL
     *      (auto-detect).
     *
     * \param bool $delay
     *      (optional) Whether to delay the actual
     *      opening of the file until the first write.
     *      Defaults to \a FALSE (no delay).
     *
     * \param bool $utc
     *      Whether the dates and times used in the backups'
     *      name should use UTC (\a TRUE) or local time
     *      (\a FALSE).
     */
    public function __construct(
        $filename,
        $when           = 'h',
        $interval       = 1,
        $backupCount    = 0,
        $encoding       = NULL,
        $delay          = 0,
        $utc            = FALSE
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
            if (strlen($this->_when) != 2) {
                throw new Plop_Exception(
                    sprintf(
                        'You must specify a day for weekly rollover '.
                        'from 0 to 6 (0 is Monday): %s',
                        $this->_when
                    )
                );
            }

            $day = ord($this->_when[1]) - ord('0');
            if ($day < 0 || $day > 6) {
                throw new Plop_Exception(
                    sprintf(
                        'Invalid day specified for weekly rollover: %s',
                        $this->_when
                    )
                );
            }

            $this->_dayOfWeek = $day;
            $this->_suffix = '%Y-%m-%d';
            $this->_extMatch = '^\\d{4}-\\d{2}-\\d{2}$';
        }
        else {
            throw new Plop_Exception(
                sprintf(
                    'Invalid rollover interval specified: %s',
                    $this->_when
                )
            );
        }
        $this->_interval    = $this->_interval * $interval;
        $this->_rolloverAt  = $this->_computeRollover(time());
    }

    /**
     * Determine when the next log rotation
     * should take place.
     *
     * \param int $currentTime
     *      Current time, as a UNIX timestamp.
     *
     * \retval int
     *      UNIX timestamp for the next log rotation.
     */
    protected function _computeRollover($currentTime)
    {
        if ($this->_when == 'MIDNIGHT') {
            return strtotime("midnight + 1 day", $currentTime);
        }

        if (substr($this->_when, 0, 1) == 'W') {
            return strtotime(
                "next " . self::$_dayNames[$this->_dayOfWeek],
                $currentTime
            );
        }

        return $currentTime + $this->_interval;
    }

    /// \copydoc Plop_Handler_RotatingAbstract::_shouldRollover().
    protected function _shouldRollover(Plop_RecordInterface $record)
    {
        $t = time();
        if ($t >= $this->_rolloverAt) {
            return TRUE;
        }
        return FALSE;
    }

    /**
     * Return a list of old log files to delete.
     *
     * \retval array
     *      List of old log files to delete.
     */
    protected function _getFilesToDelete()
    {
        $dirName    = dirname($this->_baseFilename);
        $baseName   = basename($this->_baseFilename);
        $fileNames  = scandir($dirName);
        $result     = array();
        $prefix     = $baseName . '.';
        $plen       = strlen($prefix);
        foreach ($fileNames as $fileName) {
            if ($fileName == '.' || $fileName == '..') {
                continue;
            }

            if (!strncmp($fileName, $prefix, $plen)) {
                $suffix = substr($fileName, $plen);
                if (preg_match($this->_extMatch, $suffix)) {
                    $result[] = $dirName.DIRECTORY_SEPARATOR.$fileName;
                }
            }
        }
        sort($result);
        $rlen = count($result);
        if ($rlen < $this->_backupCount) {
            $result = array();
        }
        else {
            $result = array_slice($result, 0, $rlen - $this->_backupCount);
        }
        return $result;
    }

    /// \copydoc Plop_Handler_RotatingAbstract::_doRollover().
    protected function _doRollover()
    {
        if ($this->_stream) {
            fclose($this->_stream);
        }
        $t = $this->_rolloverAt - $this->_interval;

        if ($this->_utc) {
            $formatFunc = 'gmstrftime';
        }
        else {
            $formatFunc = 'strftime';
        }

        $dfn    = $this->_baseFilename.'.'.$formatFunc($this->_suffix, $t);
        if (file_exists($dfn))
            @unlink($dfn);
        rename($this->_baseFilename, $dfn);
        if ($this->_backupCount > 0) {
            foreach ($this->_getFilesToDelete() as $s) {
                @unlink($s);
            }
        }
        $this->_mode    = 'w';
        $this->_stream  = $this->_open();
        $currentTime    = time();
        $newRolloverAt  = $this->_computeRollover($currentTime);
        while ($newRolloverAt <= $currentTime) {
            $newRolloverAt += $this->_interval;
        }
        $this->_rolloverAt = $newRolloverAt;
    }
}

