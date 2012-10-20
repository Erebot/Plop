<?php

abstract class  Plop_HandlerAbstractStub
extends         Plop_HandlerAbstract
{
    public function formatStub(Plop_RecordInterface $record)
    {
        return $this->_format($record);
    }
}

