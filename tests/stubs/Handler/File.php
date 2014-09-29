<?php

namespace Plop\Stub\Handler;

class File extends \Plop\Handler\File
{
    public function openStub()
    {
        return parent::open();
    }

    public function closeStub()
    {
        return parent::close();
    }

    public function emitStub(\Plop\RecordInterface $record)
    {
        return parent::emit($record);
    }

    public function getStreamStub()
    {
        return $this->stream;
    }
}
