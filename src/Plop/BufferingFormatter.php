<?php

class Plop_BufferingFormatter
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

Plop_BufferingFormatter::$defaultFormatter =
    new Plop_Formatter();

