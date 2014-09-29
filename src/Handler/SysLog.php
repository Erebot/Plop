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

namespace Plop\Handler;

/**
 *  \brief
 *      An handler that sends log messages to the
 *      system logs (syslog).
 */
class SysLog extends \Plop\HandlerAbstract
{
    /// Specific format string used for system logs.
    const LOG_FORMAT_STRING = "<%d>%s\000";

    /// Default address for the system logs.
    const DEFAULT_ADDRESS   = 'udg:///dev/log';

    /// Mapping between Plop's log levels and the syslog ones.
    protected static $priorityNames = array(
        'alert'     => LOG_ALERT,
        'crit'      => LOG_CRIT,
        'critical'  => LOG_CRIT,
        'debug'     => LOG_DEBUG,
        'emerg'     => LOG_EMERG,
        'err'       => LOG_ERR,
        'error'     => LOG_ERR,         // Deprecated
        'info'      => LOG_INFO,
        'notice'    => LOG_NOTICE,
        'panic'     => LOG_EMERG,       // Deprecated
        'warn'      => LOG_WARNING,     // Deprecated
        'warning'   => LOG_WARNING,
    );

    /// Mapping between the facility names and their associated constant.
    protected static $facilityNames = array(
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
    );

    /**
     *  From python's logging/handlers.py :
     *  This map appears to be trivially lowercasing the key.
     *  However, there's more to it than meets the eye - in some locales,
     *  lowercasing gives unexpected results.
     *  See SF #1524081: in the Turkish locale,
     *  \verbatim "INFO".lower() != "info" \endverbatim
     *  The same is true for PHP.
     */
    protected static $priorityMap = array(
        'DEBUG'     => 'debug',
        'INFO'      => 'info',
        'WARNING'   => 'warning',
        'ERROR'     => 'error',
        'CRITICAL'  => 'critical',
    );

    /// Address of the syslog where the logs will be sent.
    protected $address;

    /// The facility to use when logging the messages.
    protected $facility;

    /// The socket that will be used to send the logs.
    protected $socket;

    /**
     * Construct a new instance of this handler.
     *
     * \param string $address
     *      (optional) Address of the syslog
     *      where the log messages will be sent.
     *      The default is to use the value of the
     *      Plop::Handler::SysLog::DEFAULT_ADDRESS
     *      constant.
     *
     * \param int|string $facility
     *      (optional) The name or the value of the
     *      facility to use when sending the logs.
     *      By default, the "user" facility is used.
     */
    public function __construct(
        $address = self::DEFAULT_ADDRESS,
        $facility = LOG_USER
    ) {
        if (defined('LOG_LOCAL0') && !isset(static::$facilityNames['local0'])) {
            static::$facilityNames += array(
                'local0'    => LOG_LOCAL0,
                'local1'    => LOG_LOCAL1,
                'local2'    => LOG_LOCAL2,
                'local3'    => LOG_LOCAL3,
                'local4'    => LOG_LOCAL4,
                'local5'    => LOG_LOCAL5,
                'local6'    => LOG_LOCAL6,
                'local7'    => LOG_LOCAL7,
            );
        }

        parent::__construct();
        $this->address  = $address;
        $this->facility = $facility;
        $this->socket   = $this->makeSocket();
        if ($this->socket === false) {
            throw new \Plop\Exception('Unable to connect to the syslog');
        }
    }

    /// Free the resources used by this handler.
    public function __destruct()
    {
        $this->close();
    }

    /**
     * Create the socket used to communicate
     * with the syslog.
     *
     * \retval resource
     *      The newly-created socket.
     *
     * @codeCoverageIgnore
     */
    protected function makeSocket()
    {
        return stream_socket_client($this->address);
    }

    /**
     * Encode facility & priority information.
     *
     * \param int|string $facility
     *      The facility.
     *
     * \param int|string $priority
     *      The priority.
     *
     * \retval int
     *      The facility & priority, combined
     *      in a single integer.
     */
    protected function encodePriority($facility, $priority)
    {
        if (is_string($facility)) {
            $facility = static::$facilityNames[$facility];
        }
        if (is_string($priority)) {
            $priority = static::$priorityNames[$priority];
        }
        return ($facility << 3) | $priority;
    }

    /**
     * Close the socket associated with this handler.
     *
     * \return
     *      This method does not return any value.
     */
    protected function close()
    {
        if ($this->socket !== false) {
            fclose($this->socket);
            $this->socket = false;
        }
    }

    /**
     * Return the syslog priority associated with
     * the given (Plop) level name.
     *
     * \param string $levelName
     *      The name of a log level defined in Plop.
     *
     * \retval string
     *      The syslog level to use to match the one
     *      given.
     */
    protected function mapPriority($levelName)
    {
        if (isset(static::$priorityMap[$levelName])) {
            return static::$priorityMap[$levelName];
        }
        return "warning";
    }

    /// \copydoc Plop::HandlerAbstract::emit().
    protected function emit(\Plop\RecordInterface $record)
    {
        $msg = $this->format($record);
        $msg = sprintf(
            static::LOG_FORMAT_STRING,
            $this->encodePriority(
                $this->facility,
                $this->mapPriority($record['levelname'])
            ),
            $msg
        );

        for ($written = 0; $written < strlen($msg); $written += $fwrite) {
            $fwrite = @fwrite($this->socket, substr($msg, $written));
            if ($fwrite === false) {
                $this->handleError(
                    $record,
                    new \Plop\Exception('Connection lost')
                );
                break;
            }
        }
    }
}
