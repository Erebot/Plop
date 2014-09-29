<?php

namespace Plop\Stub\Handler;

abstract class RotatingAbstract extends \Plop\Handler\RotatingAbstract
{
    public function emitStub(\Plop\RecordInterface $record)
    {
        return parent::emit($record);
    }
}
