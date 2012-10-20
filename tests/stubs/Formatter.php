<?php

class   Plop_FormatterStub
extends Plop_Formatter
{
    public function formatTimeStub(
        Plop_RecordInterface    $record,
                                $dateFormat = self::DEFAULT_DATE_FORMAT
    )
    {
        return $this->_formatTime($record, $dateFormat);
    }

    public function formatExceptionStub(Exception $exception)
    {
        return $this->_formatException($exception);
    }
}

