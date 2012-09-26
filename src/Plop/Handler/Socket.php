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
extends Plop_HandlerAbstract
{
    protected $_host;
    protected $_port;
    protected $_sock;
    protected $_closeOnError;
    protected $_retryTime;
    protected $_retryStart;
    protected $_retryMax;
    protected $_retryFactor;
    protected $_retryPeriod;

    public function __construct($host, $port)
    {
        parent::__construct();
        $this->_host            = $host;
        $this->_port            = $port;
        $this->_sock            = FALSE;
        $this->_closeOnError    = 0;
        $this->_retryTime       = NULL;
        $this->_retryStart      = 1.0;
        $this->_retryMax        = 30.0;
        $this->_retryFactor     = 2.0;
        $this->_retryPeriod     = 0;
    }

    public function __destruct()
    {
        $this->_close();
    }

    protected function _makeSocket($timeout=1)
    {
        return fsockopen(
            'tcp://' . $this->_host,
            $this->_port,
            $errno,
            $errstr,
            $timeout
        );
    }

    protected function _createSocket()
    {
        $now = time();
        if ($this->_retryTime === NULL)
            $attempt = TRUE;
        else
            $attempt = ($now >= $this->_retryTime);
        if (!$attempt)
            return;
        $this->_sock = $this->_makeSocket();
        if ($this->_sock !== FALSE) {
            $this->_retryTime = NULL;
            return;
        }
        if ($this->_retryTime === NULL)
            $this->_retryPeriod = $this->_retryStart;
        else {
            $this->_retryPeriod *= $this->_retryFactor;
            if ($this->_retryPeriod > $this->_retryMax)
                $this->_retryPeriod = $this->_retryMax;
        }
        $this->_retryTime = $now + $this->_retryPeriod;
    }

    protected function _send($s)
    {
        if (!$this->_sock)
            $this->_createSocket();
        if (!$this->_sock)
            return;
        $len = strlen($s);
        for ($written = 0; $written < $len; $written += $fwrite) {
            $fwrite = fwrite($this->_sock, substr($s, $written));
            if ($fwrite === FALSE) {
                fclose($this->_sock);
                $this->_sock = FALSE;
                return;
            }
        }
    }

    protected function _makePickle(Plop_RecordInterface $record)
    {
        // To maintain full compatibility with Python,
        // we should emulate pickle here, but it seems
        // to be quite some work and PHP already has
        // it's own serialization mechanism anyway.
        $s      = serialize($record->dict);
        $slen   = pack('N', strlen($s));
        return $slen.$s;
    }

    public function handleError(Plop_RecordInterface $record, Exception $exc)
    {
        if ($this->_closeOnError && $this->_sock) {
            fclose($this->_sock);
            $this->_sock = FALSE;
        }
        else
            parent::handleError($record, $exc);
    }

    protected function _emit(Plop_RecordInterface $record)
    {
        try {
            $s = $this->_makePickle($record);
            $this->_send($s);
        }
        catch (Exception $e) {
            $this->_handleError($record, $e);
        }
    }

    public function _close()
    {
        if ($this->_sock) {
            fclose($this->_sock);
            $this->_sock = FALSE;
        }
    }
}

