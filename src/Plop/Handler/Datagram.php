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

class   Plop_Handler_Datagram
extends Plop_Handler_Socket
{
    public function __construct($host, $port)
    {
        parent::__construct($host, $port);
        $this->_closeOnError = 0;
    }

    protected function _makeSocket()
    {
        return fsockopen('udp://'.$this->_host, $this->_port);
    }

    protected function _send($s)
    {
        // PHP's way is different from Python's way here.
        // We don't need to override send(), but this is
        // done so that people don't get confused over a
        // missing method while looking at the two codes
        // side by side.
        parent::_send($s);
    }
}

