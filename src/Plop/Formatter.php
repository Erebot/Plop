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

class       Plop_Formatter
implements  Plop_FormatterInterface
{
    const DEFAULT_DATE_FORMAT = "Y-m-d H:i:s,u";

    protected $_format;
    protected $_dateFormat;
    protected $_pythonLike;

    public function __construct(
        $format     = '%(message)s',
        $dateFormat = self::DEFAULT_DATE_FORMAT
    )
    {
        $this->setFormat($format);
        $this->setDateFormat($dateFormat);
        $this->_pythonLike = FALSE;
    }

    public function getFormat()
    {
        return $this->_format;
    }

    public function setFormat($format)
    {
        $this->_format = $format;
        return $this;
    }

    public function getDateFormat()
    {
        return $this->_dateFormat;
    }

    public function setDateFormat($dateFormat)
    {
        $this->_dateFormat = $dateFormat;
        return $this;
    }

    public function format(Plop_RecordInterface $record)
    {
        $record['message'] = $record->getMessage();
        $format = (string) $this->_format;
        $dateFormat = (string) $this->_dateFormat;
        if (strpos($format, '%(asctime)') !== FALSE) {
            $record['asctime'] = $this->_formatTime($record, $dateFormat);
        }

        $s = $record->formatPercent($format, $record->asDict());
        if ($record['exc_info']) {
            if (!$record['exc_text']) {
                $record['exc_text'] =
                    $this->_formatException($record['exc_info']);
            }
        }
        if ($record['exc_text']) {
            if (substr($s, -1) != "\n") {
                $s .= "\n";
            }
            $s .= $record['exc_text'];
        }
        return $s;
    }

    protected function _formatTime(
        Plop_RecordInterface    $record,
                                $dateFormat = self::DEFAULT_DATE_FORMAT
    )
    {
        return $record['createdDate']->format((string) $dateFormat);
    }

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
            $traces[] = 'File "'.$trace['file'].'", line '.
                $trace['line'].', in '.$origin;
        }
        $s .= implode("\n", array_reverse($traces))."\n";
        $s .=   "Exception '" . get_class($exception) .
                "' with message '" . $exception->getMessage() .
                "' in " . $exception->getFile() .
                ":" . $exception->getLine();
        return $s;
    }
}

