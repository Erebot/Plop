<?php
/*
    This file is part of Plop.

    Plop is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    Plop is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with Plop.  If not, see <http://www.gnu.org/licenses/>.
*/

namespace PEAR2\Plop\Handler;

class   Datagram
extends Socket
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

