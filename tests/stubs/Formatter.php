<?php

namespace Plop\Stub;

class Formatter extends \Plop\Formatter
{
    public function formatTimeStub(
        \Plop\RecordInterface $record,
        $dateFormat = self::DEFAULT_DATE_FORMAT
    ) {
        return parent::formatTime($record, $dateFormat);
    }

    public function formatExceptionStub(\Exception $exception)
    {
        return parent::formatException($exception);
    }
}
