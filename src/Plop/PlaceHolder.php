<?php

class Plop_PlaceHolder
{
    public $loggerMap;

    public function __construct(Plop_Logger &$alogger)
    {
        $this->loggerMap = array($alogger);
    }

    public function append(Plop_Logger &$alogger)
    {
        $key = array_search($alogger, $this->loggerMap, TRUE);
        if ($key === FALSE)
            $this->loggerMap[] = $alogger;
    }
}

