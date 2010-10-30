<?php

class   Plop_Handler_SysLog
extends Plop_Handler
{
    const LOG_FORMAT_STRING = "<%d>%s\000";

    static public $priorityNames = array(
        'alert'     => LOG_ALERT,
        'crit'      => LOG_CRIT,
        'critical'  => LOG_CRIT,
        'debug'     => LOG_DEBUG,
        'emerg'     => LOG_EMERG,
        'err'       => LOG_ERR,
        'error'     => LOG_ERR,         # Deprecated
        'info'      => LOG_INFO,
        'notice'    => LOG_NOTICE,
        'panic'     => LOG_EMERG,       # Deprecated
        'warn'      => LOG_WARNING,     # Deprecated
        'warning'   => LOG_WARNING,
    );

    static public $facilityNames = array(
        'auth'      => LOG_AUTH,
        'authpriv'  => LOG_AUTHPRIV,
        'cron'      => LOG_CRON,
        'daemon'    => LOG_DAEMON,
        'kern'      => LOG_KERN,
        'lpr'       => LOG_LPR,
        'mail'      => LOG_MAIL,
        'news'      => LOG_NEWS,
        'syslog'    => LOG_SYSLOG,
        'user'      => LOG_USER,
        'uucp'      => LOG_UUCP,
        'local0'    => LOG_LOCAL0,
        'local1'    => LOG_LOCAL1,
        'local2'    => LOG_LOCAL2,
        'local3'    => LOG_LOCAL3,
        'local4'    => LOG_LOCAL4,
        'local5'    => LOG_LOCAL5,
        'local6'    => LOG_LOCAL6,
        'local7'    => LOG_LOCAL7,
    );

    /*
        From python's logging/handlers.py :
        The map below appears to be trivially lowercasing the key. However,
        there's more to it than meets the eye - in some locales, lowercasing
        gives unexpected results. See SF #1524081: in the Turkish locale,
        "INFO".lower() != "info"
     */
    static public $priorityMap = array(
        'DEBUG'     => 'debug',
        'INFO'      => 'info',
        'WARNING'   => 'warning',
        'ERROR'     => 'error',
        'CRITICAL'  => 'critical',
    );

    public $address;
    public $facility;
    public $socket;

    public function __construct($address='udg:///dev/log', $facility=LOG_USER)
    {
        parent::__construct();
        $this->address      = $address;
        $this->facility     = $facility;
        $this->socket       = stream_socket_client($address);
        if ($this->socket === FALSE)
            throw new Exception('Unable to connect to the syslog');
        $this->formatter    = NULL;
    }

    public function encodePriority($facility, $priority)
    {
        if (is_string($facility))
            $facility = self::$facilityNames[$facility];
        if (is_string($priority))
            $priority = self::$priorityNames[$priority];
        return ($facility << 3) | $priority;
    }

    public function close()
    {
        fclose($this->socket);
        parent::close();
    }

    public function mapPriority($levelName)
    {
        if (isset(self::$priorityMap[$levelName]))
            return self::$priorityMap[$levelName];
        return "warning";
    }

    public function emit(Plop_Record &$record)
    {
        $msg = $this->format($record);
        $msg = sprintf(
            self::LOG_FORMAT_STRING,
            $this->encodePriority(
                $this->facility,
                $this->mapPriority($record->dict['levelname'])
            ),
            $msg
        );

        try {
            fwrite($this->socket, $msg, strlen($msg));
        }
        catch (Exception $e) {
            $this->handleError($record, $e);
        }
    }
}

