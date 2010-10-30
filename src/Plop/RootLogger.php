<?php

class   Plop_RootLogger
extends Plop_Logger
{
    public function __construct($level)
    {
        parent::__construct("root", $level);
    }
}


