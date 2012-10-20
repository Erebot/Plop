<?php

class   Plop_Formatter_Stub
extends Plop_Formatter
{
    public function formatTimeStub(
        Plop_RecordInterface    $record,
                                $dateFormat = self::DEFAULT_DATE_FORMAT
    )
    {
        return parent::_formatTime($record, $dateFormat);
    }

    public function formatExceptionStub(Exception $exception)
    {
        return parent::_formatException($exception);
    }
}

