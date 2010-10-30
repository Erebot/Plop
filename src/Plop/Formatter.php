<?php

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

