<?php

interface Plop_RecordInterface_Helper
{
    public function formatPercent($msg, array $args = array());
}

abstract class  Plop_RecordInterface_Stub
implements      Plop_RecordInterface
{
    static protected $_formatPercentHelper = NULL;

    static public function formatPercent($msg, array $args = array())
    {
        $helper = self::$_formatPercentHelper;
        return $helper->formatPercent($msg, $args);
    }

    final public function setFormatPercentHelper(
        Plop_RecordInterface_Helper $helper
    )
    {
        self::$_formatPercentHelper = $helper;
    }
}

