<?php

class   ErebotLoggingSocketHandler
extends ErebotLoggingHandler
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
        return fsockopen('tcp://'.$this->host, $this->port,
                            $errno, $errstr, $timeout);
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

    public function makePickle(ErebotLoggingRecord &$record)
    {
        /// @TODO Should we follow Python's pickle here instead ?
        $s      = serialize($record->dict);
        $slen   = pack('N', strlen($s));
        return $slen.$s;
    }

    public function handleError(ErebotLoggingRecord &$record, Exception &$exc)
    {
        if ($this->closeOnError && $this->sock) {
            fclose($this->sock);
            $this->sock = FALSE;
        }
        else
            parent::handleError($record, $exc);
    }

    public function emit(ErebotLoggingRecord &$record)
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

?>
