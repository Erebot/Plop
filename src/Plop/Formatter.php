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

class Plop_Formatter
{
    public $fmt;
    public $datefmt;
    public $converter;

    public function __construct($fmt = NULL, $datefmt = NULL)
    {
        if ($fmt === NULL)
            $this->fmt = '%(message)s';
        else
            $this->fmt = $fmt;
        $this->datefmt = $datefmt;
    }

    public function format(Plop_Record $record)
    {
        $record->dict['message'] = $record->getMessage();
        if (strpos($this->fmt, '%(asctime)') !== FALSE)
            $record->dict['asctime'] = $this->formatTime(
                $record, $this->datefmt
            );
        $s = $record->formatPercent($this->fmt, $record->dict);
        if ($record->dict['exc_info'])
            if (!$record->dict['exc_text'])
                $record->dict['exc_text'] = $this->formatException(
                    $record->dict['exc_info']
                );
        if ($record->dict['exc_text']) {
            if (substr($s, -1) != "\n")
                $s .= "\n";
            $s .= $record->dict['exc_text'];
        }
        return $s;
    }

    public function formatTime(Plop_Record $record, $datefmt = NULL)
    {
        $ct = $record->dict['created'];
        if ($datefmt)
            $s = strftime($datefmt, $ct);
        else
            $s = strftime("%F %T", $ct).
                sprintf(",%03d", $record->dict['msecs']);
        return $s;
    }

    public function formatException(Exception $exception)
    {
        $s  = "Traceback (most recent call last):\n";
        foreach ($exception->getTrace() as $trace) {
            $origin = '';
            if (isset($trace['class']))
                $origin = $trace['class'].$trace['type'];
            if (isset($trace['function']))
            $origin .= $trace['function'];
            if ($origin == '')
                $origin = '???';
            $traces[] = 'File "'.$trace['file'].'", line '.
                $trace['line'].', in '.$origin;
        }
        array_reverse($traces);
        $s .= implode("\n", $traces)."\n";
        $s .= (string) $exception;
        if (substr($s, -1) == "\n")
            $s = substr($s, 0, -1);
        return $s;
    }
}

