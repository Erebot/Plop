<?php

class   Plop_Stub
extends Plop
{
    public function __construct()
    {
        parent::__construct();
        $this->_created = 12345678.9;
    }

    static public function resetInstanceStub()
    {
        self::$_instance = NULL;
    }
}

