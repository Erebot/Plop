<?php

namespace Plop\Stub;

abstract class RecordInterface implements \Plop\RecordInterface
{
    protected static $formatPercentHelper = null;

    public static function formatPercent($msg, array $args = array())
    {
        $helper = self::$formatPercentHelper;
        return $helper->formatPercent($msg, $args);
    }

    final public function setFormatPercentHelper(
        \Plop\Stub\RecordInterface\Helper $helper
    ) {
        self::$formatPercentHelper = $helper;
    }
}
