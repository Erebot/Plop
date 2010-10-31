<?php

namespace PEAR2\Plop;

class PlaceHolder
{
    public $loggerMap;

    public function __construct(Logger &$alogger)
    {
        $this->loggerMap = array($alogger);
    }

    public function append(Logger &$alogger)
    {
        $key = array_search($alogger, $this->loggerMap, TRUE);
        if ($key === FALSE)
            $this->loggerMap[] = $alogger;
    }
}

