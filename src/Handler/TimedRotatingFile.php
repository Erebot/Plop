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

namespace Plop\Handler;

/**
 *  \brief
 *      An handler that writes logs to a file
 *      but also handles that file's rotation,
 *      based on time.
 */
class TimedRotatingFile extends \Plop\Handler\RotatingAbstract
{
    /// Log rotation specification (eg. 'W' or 'MIDNIGHT').
    protected $when;

    /// Number of backup log files to keep.
    protected $backupCount;

    /// Whether the files are named based on UTC time or local time.
    protected $utc;

    /// Interval between file rotations.
    protected $interval;

    /// The date format that will be used as a suffix for the log files.
    protected $suffix;

    /// A PCRE pattern that matches rotated files.
    protected $extMatch;

    /// Day of week (0=Monday...6=Sunday) when the log rotation happens.
    protected $dayOfWeek;

    /// UNIX timestamp of the next log rotation.
    protected $rolloverAt;

    /// The names of days in English, starting with Monday.
    protected static $dayNames = array(
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
     * \param bool $delay
     *      (optional) Whether to delay the actual
     *      opening of the file until the first write.
     *      Defaults to \a false (no delay).
     *
     * \param bool $utc
     *      Whether the dates and times used in the backups'
     *      name should use UTC (\a true) or local time
     *      (\a false).
     */
    public function __construct(
        $filename,
        $when = 'h',
        $interval = 1,
        $backupCount = 0,
        $delay = false,
        $utc = false
    ) {
        parent::__construct($filename, 'a', $delay);
        $this->when         = strtoupper($when);
        $this->backupCount  = $backupCount;
        $this->utc          = $utc;
        $this->rolloverAt   = null;
        $this->dayOfWeek    = null;

        if ($this->when == 'S') {
            $this->interval     = 1;
            $this->suffix       = '%Y-%m-%d_%H-%M-%S';
            $this->extMatch     = '^\\d{4}-\\d{2}-\\d{2}_\\d{2}-\\d{2}-\\d{2}$';
        } elseif ($this->when == 'M') {
            $this->interval     = 60;
            $this->suffix       = '%Y-%m-%d_%H-%M';
            $this->extMatch     = '^\\d{4}-\\d{2}-\\d{2}_\\d{2}-\\d{2}$';
        } elseif ($this->when == 'H') {
            $this->interval     = 60 * 60;
            $this->suffix       = '%Y-%m-%d_%H';
            $this->extMatch     = '^\\d{4}-\\d{2}-\\d{2}_\\d{2}$';
        } elseif ($this->when == 'D' || $this->when == 'MIDNIGHT') {
            $this->interval     = 60 * 60 * 24;
            $this->suffix       = '%Y-%m-%d';
            $this->extMatch     = '^\\d{4}-\\d{2}-\\d{2}$';
        } elseif (substr($this->when, 0, 1) == 'W') {
            $this->interval = 60 * 60 * 24 * 7;
            if (strlen($this->when) != 2) {
                throw new \Plop\Exception(
                    sprintf(
                        'You must specify a day for weekly rollover '.
                        'from 0 to 6 (0 is Monday), not %s',
                        $this->when
                    )
                );
            }

            $day = ord($this->when[1]) - ord('0');
            if ($day < 0 || $day > 6) {
                throw new \Plop\Exception(
                    sprintf(
                        'Invalid day specified for weekly rollover: %s',
                        $this->when[1]
                    )
                );
            }

            $this->dayOfWeek    = $day;
            $this->suffix       = '%Y-%m-%d';
            $this->extMatch     = '^\\d{4}-\\d{2}-\\d{2}$';
        } else {
            throw new \Plop\Exception(
                sprintf(
                    'Invalid rollover interval specified: %s',
                    $this->when
                )
            );
        }

        if (!is_int($interval) || $interval < 1) {
            throw new \Plop\Exception(
                'The interval should be an integer ' .
                'greater than or equal to 1'
            );
        }

        $this->interval     = $this->interval * $interval;
        $this->rolloverAt   = $this->computeRollover($this->getTime());
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
    protected function computeRollover($currentTime)
    {
        if ($this->when == 'MIDNIGHT') {
            return strtotime("midnight + 1 day", $currentTime);
        }

        if (substr($this->when, 0, 1) == 'W') {
            return strtotime(
                "next " . self::$dayNames[$this->dayOfWeek],
                $currentTime
            );
        }

        return $currentTime + $this->interval;
    }

    /**
     * Return the current time, as a UNIX timestamp.
     *
     * \retval int
     *      Current time, as a UNIX timestamp.
     *
     * @codeCoverageIgnore
     */
    protected function getTime()
    {
        return time();
    }

    /// \copydoc Plop::Handler::RotatingAbstract::shouldRollover().
    protected function shouldRollover(\Plop\RecordInterface $record)
    {
        return ($this->getTime() >= $this->rolloverAt);
    }

    /**
     * Return a list of old log files to delete.
     *
     * \retval array
     *      List of old log files to delete.
     */
    protected function getFilesToDelete()
    {
        $dirName    = dirname($this->baseFilename);
        $baseName   = basename($this->baseFilename);
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
                if (preg_match($this->extMatch, $suffix)) {
                    $result[] = $dirName . DIRECTORY_SEPARATOR . $fileName;
                }
            }
        }

        sort($result);
        $rlen = count($result);
        if ($rlen < $this->backupCount) {
            $result = array();
        } else {
            $result = array_slice($result, 0, $rlen - $this->backupCount);
        }
        return $result;
    }

    /// \copydoc Plop::Handler::RotatingAbstract::doRollover().
    protected function doRollover()
    {
        if (is_resource($this->stream)) {
            fclose($this->stream);
        }

        $t = $this->rolloverAt - $this->interval;

        if ($this->utc) {
            $formatFunc = 'gmstrftime';
        } else {
            $formatFunc = 'strftime';
        }

        $dfn = $this->baseFilename . '.' . $formatFunc($this->suffix, $t);
        if (file_exists($dfn)) {
            @unlink($dfn);
        }

        rename($this->baseFilename, $dfn);
        if ($this->backupCount > 0) {
            foreach ($this->getFilesToDelete() as $s) {
                @unlink($s);
            }
        }

        $this->mode     = 'w';
        $this->stream   = $this->open();
        $currentTime    = time();
        $newRolloverAt  = $this->computeRollover($currentTime);
        while ($newRolloverAt <= $currentTime) {
            $newRolloverAt += $this->interval;
        }
        $this->rolloverAt = $newRolloverAt;
    }
}
