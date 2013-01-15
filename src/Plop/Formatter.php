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
 *      A class that provides basic formatting
 *      for log records.
 */
class       Plop_Formatter
implements  Plop_FormatterInterface
{
    /// Default format for log messages.
    const DEFAULT_FORMAT = '%(message)s';

    /// Default format for dates.
    const DEFAULT_DATE_FORMAT = "Y-m-d H:i:s,u";

    /// General format for log records.
    protected $_format;

    /// Format to use for dates/times.
    protected $_dateFormat;

    /// Timezone to use to represent dates/times.
    protected $_timezone;

    /// Whether _formatException() generates Python-like traces.
    protected $_pythonLike;

    /**
     * Construct a new formatter.
     *
     * \param string $format
     *      (optional) The format specification
     *      for log records, which may contain
     *      percent-sequences.
     *      Defaults to the value of the
     *      Plop_Formatter::DEFAULT_FORMAT constant.
     *
     * \param string $dateFormat
     *      (optional) The format specification
     *      to use to format dates.
     *      The default is to use the value of the
     *      Plop_Formatter::DEFAULT_DATE_FORMAT
     *      constant.
     *
     * \param DateTimeZone|string $timezone
     *      (optional) Timezone to use when formatting dates/times.
     *      This must be a valid timezone (see http://php.net/timezones.php).
     *      Defaults to using the default timezone, as returned by
     *      \a date_default_timezone_get().
     *
     * \param bool $pythonLike
     *      (optional) Whether exceptions should be formatted
     *      to resemble Python stack traces (\a TRUE) or if they
     *      should keep their original PHP format (\a FALSE).
     *      Defaults to \a FALSE (display PHP stack traces).
     *
     * \see
     *      Plop_RecordInterface::formatPercent()
     *      for more information on percent-sequences.
     *
     * \see
     *      The documentation from the PHP manual on the
     *      <a href="http://php.net/date">date()</a>
     *      function for a list of valid format specifiers
     *      for the \a $dateFormat parameter.
     */
    public function __construct(
        $format     = self::DEFAULT_FORMAT,
        $dateFormat = self::DEFAULT_DATE_FORMAT,
        $timezone   = NULL,
        $pythonLike = FALSE
    )
    {
        if ($timezone !== NULL) {
            if (is_string($timezone)) {
                $timezone = new DateTimeZone($timezone);
            }
            else if (!is_object($timezone) ||
                     !($timezone instanceof DateTimeZone)) {
                throw new Plop_Exception('Invalid timezone');
            }
        }
        $this->setFormat($format);
        $this->setDateFormat($dateFormat);
        $this->setTimeZone($timezone);
        $this->setPythonLike($pythonLike);
    }

    /// \copydoc Plop_FormatterInterface::getFormat().
    public function getFormat()
    {
        return $this->_format;
    }

    /// \copydoc Plop_FormatterInterface::setFormat().
    public function setFormat($format)
    {
        $this->_format = $format;
        return $this;
    }

    /// \copydoc Plop_FormatterInterface::getDateFormat().
    public function getDateFormat()
    {
        return $this->_dateFormat;
    }

    /// \copydoc Plop_FormatterInterface::setDateFormat().
    public function setDateFormat($dateFormat)
    {
        $this->_dateFormat = $dateFormat;
        return $this;
    }

    /// \copydoc Plop_FormatterInterface::getTimezone().
    public function getTimezone()
    {
        return $this->_timezone;
    }

    /// \copydoc Plop_FormatterInterface::setTimezone().
    public function setTimezone(DateTimeZone $timezone = NULL)
    {
        $this->_timezone = $timezone;
        return $this;
    }

    /**
     * Return whether exceptions are rendered like
     * Python stack traces or not.
     *
     * \retval bool
     *      \a TRUE if the exceptions will look like
     *      Python stack traces or \a FALSE if they
     *      will look like regular PHP stack traces.
     */
    public function getPythonLike()
    {
        return $this->_pythonLike;
    }

    /**
     * Set the format used to render exceptions.
     *
     * \param bool $pythonLike
     *      If this is \a TRUE, exception stack traces
     *      will be rendered using a format close to
     *      the one Python uses. Otherwise, the original
     *      (PHP) format is kept.
     *
     * \retval Plop_FormatterInterface
     *      The formatter instance (ie. \a $this).
     */
    public function setPythonLike($pythonLike)
    {
        if (!is_bool($pythonLike)) {
            throw new Plop_Exception('Invalid value');
        }
        $this->_pythonLike = $pythonLike;
        return $this;
    }

    /// \copydoc Plop_FormatterInterface::format().
    public function format(Plop_RecordInterface $record)
    {
        $record['message'] = $record->getMessage();
        $format = (string) $this->_format;
        $dateFormat = (string) $this->_dateFormat;
        if (strpos($format, '%(asctime)') !== FALSE) {
            $record['asctime'] = $this->_formatTime($record, $dateFormat);
        }

        $s = $record->formatPercent($format, $record->asArray());
        if ($record['exc_info']) {
            if (!$record['exc_text']) {
                $record['exc_text'] =
                    $this->_formatException($record['exc_info']);
            }
        }
        if ($record['exc_text']) {
            $s .= "\n" . $record['exc_text'];
        }
        return $s;
    }

    /**
     * Format the creation date of a log record.
     *
     * \param Plop_RecordInterface $record
     *      The log record whose creation date will be
     *      formatted.
     *
     * \param string $dateFormat
     *      (optional) The format to apply.
     *      By default, Plop_Formatter::DEFAULT_DATE_FORMAT
     *      is used.
     *
     * \see
     *      The documentation from the PHP manual on the
     *      <a href="http://php.net/date">date()</a>
     *      function for a list of valid format specifiers.
     */
    protected function _formatTime(
        Plop_RecordInterface    $record,
                                $dateFormat = self::DEFAULT_DATE_FORMAT
    )
    {
        $date = clone $record['createdDate'];
        if ($this->_timezone !== NULL) {
            $date->setTimeZone($this->_timezone);
        }
        return $date->format((string) $dateFormat);
    }

    /**
     * Format an exception.
     *
     * \param Exception $exception
     *      The exception to format.
     *
     * \retval string
     *      The full trace of the exception,
     *      with proper formatting applied.
     */
    protected function _formatException(Exception $exception)
    {
        // Don't display exceptions unless display_errors
        // is set to "On" (which ini_get() returns as "1").
        if (!((int) ini_get("display_errors")))
            return FALSE;

        if (!$this->_pythonLike) {
            $s = (string) $exception;
            if (substr($s, -1) == "\n")
                $s = substr($s, 0, -1);
            return $s;
        }

        $s      = "Traceback (most recent call last):\n";
        $traces = array();
        foreach ($exception->getTrace() as $trace) {
            $origin = '';
            if (isset($trace['class']))
                $origin = $trace['class'].$trace['type'];
            if (isset($trace['function']))
                $origin .= $trace['function'].'()';
            if ($origin == '')
                $origin = '???';
            $file = isset($trace['file']) ? $trace['file'] : '{main}';
            $line = isset($trace['line']) ? $trace['line'] : 0;
            $traces[] = "File \"$file\", line $line, in $origin";
        }
        $s .= implode("\n", array_reverse($traces))."\n";
        $s .=   "Exception '" . get_class($exception) .
                "' with message '" . $exception->getMessage() .
                "' in " . $exception->getFile() .
                ":" . $exception->getLine();
        return $s;
    }
}

