<?php

namespace Plop\Stub;

abstract class HandlerAbstract extends \Plop\HandlerAbstract
{
    public function formatStub(\Plop\RecordInterface $record)
    {
        return parent::format($record);
    }
}
