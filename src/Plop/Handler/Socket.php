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

class   Plop_Handler_Socket
extends Plop_Handler
{
    public $host;
    public $port;
    public $sock;
    public $closeOnError;
    public $retryTime;
    public $retryStart;
    public $retryMax;
    public $retryFactor;
    public $retryPeriod;

    public function __construct($host, $port)
    {
        parent::__construct();
        $this->host         = $host;
        $this->port         = $port;
        $this->sock         = FALSE;
        $this->closeOnError = 0;
        $this->retryTime    = NULL;
        $this->retryStart   = 1.0;
        $this->retryMax     = 30.0;
        $this->retryFactor  = 2.0;
        $this->retryPeriod  = 0;
    }

    public function makeSocket($timeout=1)
    {
        return fsockopen(
            'tcp://' . $this->host,
            $this->port,
            $errno,
            $errstr,
            $timeout
        );
    }

    public function createSocket()
    {
        $now = time();
        if ($this->retryTime === NULL)
            $attempt = TRUE;
        else
            $attempt = ($now >= $this->retryTime);
        if (!$attempt)
            return;
        $this->sock = $this->makeSocket();
        if ($this->sock !== FALSE) {
            $this->retryTime = NULL;
            return;
        }
        if ($this->retryTime === NULL)
            $this->retryPeriod = $this->retryStart;
        else {
            $this->retryPeriod *= $this->retryFactor;
            if ($this->retryPeriod > $this->retryMax)
                $this->retryPeriod = $this->retryMax;
        }
        $this->retryTime = $now + $this->retryPeriod;
    }

    public function send($s)
    {
        if (!$this->sock)
            $this->createSocket();
        if (!$this->sock)
            return;
        $len = strlen($s);
        for ($written = 0; $written < $len; $written += $fwrite) {
            $fwrite = fwrite($this->sock, substr($s, $written));
            if ($fwrite === FALSE) {
                fclose($this->sock);
                $this->sock = FALSE;
                return;
            }
        }
    }

    public function makePickle(Plop_Record &$record)
    {
        /// @TODO Should we follow Python's pickle here instead ?
        $s      = serialize($record->dict);
        $slen   = pack('N', strlen($s));
        return $slen.$s;
    }

    public function handleError(Plop_Record &$record, Exception &$exc)
    {
        if ($this->closeOnError && $this->sock) {
            fclose($this->sock);
            $this->sock = FALSE;
        }
        else
            parent::handleError($record, $exc);
    }

    public function emit(Plop_Record &$record)
    {
        try {
            $s = $this->makePickle($record);
            $this->send($s);
        }
        catch (Exception $e) {
            $this->handleError($record, $e);
        }
    }

    public function close()
    {
        if ($this->sock) {
            fclose($this->sock);
            $this->sock = FALSE;
        }
        parent::close();
    }
}

