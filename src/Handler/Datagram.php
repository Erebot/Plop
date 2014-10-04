<?php
/*
    This file is part of Plop, a simple logging library for PHP.

    Copyright © 2010-2014 François Poirotte

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

namespace Plop\Handler;

/**
 *  \brief
 *      An handler that sends log messages
 *      to a remote host using UDP datagrams.
 */
class Datagram extends \Plop\Handler\Socket
{
    /// \copydoc Plop::Handler::Socket::__construct($host, $port).
    public function __construct($host, $port)
    {
        parent::__construct($host, $port);
        $this->closeOnError = 0;
    }

    /**
     * Create a new socket.
     *
     * \param int $timeout
     *      (optional) Unused. This parameter exists
     *      only for compatibility reasons with the
     *      base class (Plop::Handler::Socket).
     *
     * \retval resource
     *      The newly created socket.
     *
     * @codeCoverageIgnore
     */
    protected function makeSocket($timeout = 1)
    {
        return fsockopen('udp://'.$this->host, $this->port);
    }
}
