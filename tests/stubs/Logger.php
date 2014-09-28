<?php

namespace Plop\Stub;

class Logger extends \Plop\Logger
{
    public function callHandlersStub(\Plop\RecordInterface $record)
    {
        return parent::callHandlers($record);
    }

    public function emitWarningStub()
    {
        return parent::emitWarning();
    }

    public function handleStub(\Plop\RecordInterface $record)
    {
        return parent::handle($record);
    }
}
