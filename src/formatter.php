<?php

class PlopFormatter
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

    public function format(PlopRecord $record)
    {
        $record->dict['message'] = $record->getMessage();
        if (strpos($this->fmt, '%(asctime)') !== FALSE)
            $record->dict['asctime'] = $this->formatTime(
                                        $record, $this->datefmt);
        $s = $record->formatPercent($this->fmt, $record->dict);
        if ($record->dict['exc_info'])
            if (!$record->dict['exc_text'])
                $record->dict['exc_text'] = $this->formatException(
                                                $record->dict['exc_info']);
        if ($record->dict['exc_text']) {
            if (substr($s, -1) != "\n")
                $s .= "\n";
            $s .= $record->dict['exc_text'];
        }
        return $s;
    }

    public function formatTime(PlopRecord $record, $datefmt = NULL)
    {
        $ct = $record->dict['created'];
        if ($datefmt)
            $s = strftime($datefmt, $ct);
        else
            $s = strftime("%F %T", $ct).
                sprintf(",%03d", $record->dict['msecs']);
        return $s;
    }

    public function formatException(Exception $exc_info)
    {
        $s = (string) $exc_info;
        if (substr($s, -1) == "\n")
            $s = substr($s, 0, -1);
        return $s;
    }
}

class PlopBufferingFormatter
{
    static public $defaultFormatter = NULL;
    protected $lineFmt;

    public function __construct($linefmt = NULL)
    {
        if ($lineFmt)
            $this->lineFmt = $lineFmt;
        else
            $this->lineFmt = self::$defaultFormatter;
    }

    public function formatHeader($records)
    {
        return "";
    }

    public function formatFooter($records)
    {
        return "";
    }

    public function format($records)
    {
        $rv = "";
        if (count($records)) {
            $rv .= $this->formatHeader($records);
            foreach ($records as &$record) {
                $rv .= $this->lineFmt->format($record);
            }
            unset($record);
            $rv .= $this->formatFooter($records);
        }
        return $rv;
    }
}

PlopBufferingFormatter::$defaultFormatter =
    new PlopFormatter();

?>
