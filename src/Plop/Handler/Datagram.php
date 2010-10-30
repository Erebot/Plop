<?php

include_once(dirname(__FILE__).'/SocketHandler.php');

class   Plop_Handler_Datagram
extends Plop_Handler_Socket
{
    public function __construct($host, $port)
    {
        parent::__construct($host, $port);
        $this->closeOnError = 0;
    }

    public function makeSocket()
    {
        return fsockopen('udp://'.$this->host, $this->port);
    }

    public function send($s)
    {
        // PHP's way is different from Python's way here.
        // We don't need to override send(), but this is
        // done so that people don't get confused over a
        // missing method while looking at the two codes
        // side by side.
        parent::send($s);
    }
}

