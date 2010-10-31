<?php

namespace PEAR2\Plop;

class   RootLogger
extends Logger
{
    public function __construct($level)
    {
        parent::__construct("root", $level);
    }
}


