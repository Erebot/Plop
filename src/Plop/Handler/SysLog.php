<?php
/*
    This file is part of Plop, a simple logging library for PHP.

    Copyright © 2010-2012 François Poirotte

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

class   Plop_Handler_SysLog
extends Plop_HandlerAbstract
{
    const LOG_FORMAT_STRING = "<%d>%s\000";

    static protected $_priorityNames = array(
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

    static protected $_facilityNames = array(
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
        "INFO".lower() != "info". The same is true in PHP.
     */
    static protected $_priorityMap = array(
        'DEBUG'     => 'debug',
        'INFO'      => 'info',
        'WARNING'   => 'warning',
        'ERROR'     => 'error',
        'CRITICAL'  => 'critical',
    );

    protected $_address;
    protected $_facility;
    protected $_socket;

    public function __construct(
        $address    = 'udg:///dev/log',
        $facility   = LOG_USER
    )
    {
        parent::__construct();
        $this->_address     = $address;
        $this->_facility    = $facility;
        $this->_socket      = stream_socket_client($address);
        if ($this->_socket === FALSE) {
            throw new Exception('Unable to connect to the syslog');
        }
    }

    public function __destruct()
    {
        $this->_close();
    }

    protected function _encodePriority($facility, $priority)
    {
        if (is_string($facility))
            $facility = self::$_facilityNames[$facility];
        if (is_string($priority))
            $priority = self::$_priorityNames[$priority];
        return ($facility << 3) | $priority;
    }

    protected function _close()
    {
        if ($this->_socket !== FALSE) {
            fclose($this->_socket);
            $this->_socket = FALSE;
        }
    }

    protected function _mapPriority($levelName)
    {
        if (isset(self::$_priorityMap[$levelName]))
            return self::$_priorityMap[$levelName];
        return "warning";
    }

    protected function _emit(Plop_RecordInterface $record)
    {
        $msg = $this->_format($record);
        $msg = sprintf(
            self::LOG_FORMAT_STRING,
            $this->_encodePriority(
                $this->_facility,
                $this->_mapPriority($record['levelname'])
            ),
            $msg
        );

        try {
            fwrite($this->_socket, $msg);
        }
        catch (Exception $e) {
            $this->handleError($record, $e);
        }
    }
}

