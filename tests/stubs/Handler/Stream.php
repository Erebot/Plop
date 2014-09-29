<?php

namespace Plop\Stub\Handler;

class Stream extends \Plop\Handler\Stream
{
    public function emitStub(\Plop\RecordInterface $record)
    {
        return parent::emit($record);
    }
}
