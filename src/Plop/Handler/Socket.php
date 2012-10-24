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

/**
 *  \brief
 *      An handler that sends log messages to a remote host
 *      over a TCP socket.
 */
class   Plop_Handler_Socket
extends Plop_HandlerAbstract
{
    /// Remote host where the logs will be sent.
    protected $_host;

    /// Remote port where the logs will be sent.
    protected $_port;

    /// The socket that will be used to send the logs.
    protected $_socket;

    /// Whether to close the socket automatically on error.
    protected $_closeOnError;

    /// UNIX timestamp of the next connection attempt, if any.
    protected $_retryTime;

    /// Initial delay for reconnection attempts.
    protected $_retryStart;

    /// Maximum delay between reconnection attempts.
    protected $_retryMax;

    /// Factor applied to the reconnection delay after a reconnection failure.
    protected $_retryFactor;

    /// The delay that will apply to the next reconnection attempt.
    protected $_retryPeriod;

    /**
     * Construct a new instance of this handler.
     *
     * \param string $host
     *      The remote host where the logs will be sent.
     *      This may be a (fully qualified) host name or an
     *      IP address (v4 or v6).
     *
     * \param int $port
     *      Destination port where the logs will be sent.
     */
    public function __construct($host, $port)
    {
        parent::__construct();

        if (strpos($host, ':') !== FALSE) {
            // IPv6 addresses must be enclosed in brackets.
            $host = "[$host]";
        }

        $this->_host            = $host;
        $this->_port            = $port;
        $this->_socket          = FALSE;
        $this->_closeOnError    = FALSE;
        $this->_retryTime       = NULL;
        $this->_retryStart      = 1.0;
        $this->_retryMax        = 30.0;
        $this->_retryFactor     = 2.0;
        $this->_retryPeriod     = 0;
    }

    /// Free the resources used by this handler.
    public function __destruct()
    {
        $this->_close();
    }

    /**
     * Return whether the socket is closed
     * automatically after an error.
     *
     * \retval bool
     *      Whether the socket is closed
     *      automatically on error (\a TRUE)
     *      or not (\a FALSE).
     */
    public function getCloseOnError()
    {
        return $this->_closeOnError;
    }

    /**
     * Set whether the socket must be closed
     * automatically on error.
     *
     * \param bool $close
     *      The socket will be close on error
     *      if this is \a TRUE.
     *
     * \retval Plop_HandlerInterface
     *      The current handler instance (ie. \a $this).
     */
    public function setCloseOnError($close)
    {
        if (!is_bool($close)) {
            throw new Plop_Exception('Invalid value');
        }
        $this->_closeOnError = $close;
        return $this;
    }

    /**
     * Return the delay for the initial
     * reconnection attempt.
     *
     * \retval int|float
     *      Initial delay for reconnection attempts.
     */
    public function getInitialRetryDelay()
    {
        return $this->_retryStart;
    }

    /**
     * Set the delay for the initial
     * reconnection attempt.
     *
     * \param int|float $delay
     *      Initial delay for reconnection attempts.
     *      This value must be a non-negative number.
     *
     * \retval Plop_HandlerInterface
     *      The current handler instance (ie. \a $this).
     */
    public function setInitialRetryDelay($delay)
    {
        if (!(is_int($delay) || is_float($delay)) || $delay < 0) {
            throw new Plop_Exception('Invalid value');
        }
        $this->_retryStart = $delay;
        return $this;
    }

    /**
     * Return the current delay factor between
     * reconnection attempts.
     *
     * \retval int|float
     *      Current delay factor.
     */
    public function getRetryFactor()
    {
        return $this->_retryFactor;
    }

    /**
     * Set the factor applied to the delay
     * between each reconnection attempt.
     *
     * \param int|float $factor
     *      Delay factor.
     *      This value must be greater or equal to 1.
     *
     * \retval Plop_HandlerInterface
     *      The current handler instance (ie. \a $this).
     */
    public function setRetryFactor($factor)
    {
        if (!(is_int($factor) || is_float($factor)) || $factor < 1) {
            throw new Plop_Exception('Invalid value');
        }
        $this->_retryFactor = $factor;
        return $this;
    }

    /**
     * Return the maximum delay between
     * reconnection attempts.
     *
     * \retval int|float
     *      Maximum delay between reconnection attempts.
     */
    public function getMaximumRetryDelay()
    {
        return $this->_retryMax;
    }

    /**
     * Set the maximum delay between
     * reconnection attempts.
     *
     * \param int|float $max
     *      Maximum delay between reconnection attempts.
     *      This value must be a non-negative number.
     *
     * \retval Plop_HandlerInterface
     *      The current handler instance (ie. \a $this).
     */
    public function setMaximumRetryDelay($max)
    {
        if (!(is_int($max) || is_float($max)) || $max < 0) {
            throw new Plop_Exception('Invalid value');
        }
        $this->_retryMax = $max;
        return $this;
    }

    /**
     * Really create a new socket.
     *
     * \param int $timeout
     *      (optional) Timeout for the connection,
     *      in seconds. Defaults to 1 second.
     *
     * \retval resource
     *      The newly created socket.
     *
     * @codeCoverageIgnore
     */
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

    /**
     * Return the current time as a UNIX timestamp.
     *
     * \retval int
     *      The current time, as a UNIX timestamp.
     *
     * @codeCoverageIgnore
     */
    protected function _getCurrentTime()
    {
        return time();
    }

    /**
     * Create a new socket, taking into account
     * things like retry attempts and delays.
     *
     * \return
     *      This method does not return any value.
     */
    protected function _createSocket()
    {
        $now = $this->_getCurrentTime();
        if ($this->_retryTime === NULL) {
            $attempt = TRUE;
        }
        else {
            $attempt = ($now >= $this->_retryTime);
        }

        if (!$attempt) {
            return;
        }

        $this->_socket = $this->_makeSocket();
        if ($this->_socket !== FALSE) {
            $this->_retryTime = NULL;
            return;
        }

        if ($this->_retryTime === NULL) {
            $this->_retryPeriod = $this->_retryStart;
        }
        else {
            $this->_retryPeriod *= $this->_retryFactor;
            if ($this->_retryPeriod > $this->_retryMax)
                $this->_retryPeriod = $this->_retryMax;
        }
        $this->_retryTime = $now + $this->_retryPeriod;
    }

    /**
     * Send the given string over the wire.
     *
     * \param string $s
     *      The text to send over.
     *
     * \retval bool
     *      Whether a connection could be established
     *      and the data sent properly.
     *
     * \throws Plop_Exception
     *      The connection was lost during the transmission.
     */
    protected function _send($s)
    {
        if (!$this->_socket) {
            $this->_createSocket();
        }

        if (!$this->_socket) {
            return FALSE;
        }

        $written = 0;
        while ($s != '') {
            $written = $this->_write($s);
            if ($written === FALSE) {
                throw new Plop_Exception('Connection lost');
            }
            $s = (string) substr($s, $written);
        }
        return TRUE;
    }

    /**
     * Write data to the underlying stream.
     *
     * \param string $s
     *      Data to write.
     *
     * \retval FALSE|int
     *      Return the number of bytes written
     *      to the underlying stream (which can
     *      be less than the length of the data
     *      given due to buffering) or \a FALSE
     *      in case of an error (eg. connection
     *      lost).
     *
     * @codeCoverageIgnore
     */
    protected function _write($s)
    {
        return @fwrite($this->_socket, $s);
    }

    /**
     * Serialize and format a log record
     * so that it can be sent through the wire.
     *
     * \param Plop_RecordInterface $record
     *      The record to serialize.
     *
     * \retval string
     *      Serialized representation of the record,
     *      with additional metadata.
     */
    protected function _makePickle(Plop_RecordInterface $record)
    {
        // To maintain full compatibility with Python,
        // we should emulate pickle here, but it seems
        // to be quite some work and PHP already has
        // it's own serialization mechanism anyway.
        $s      = serialize($record);
        $slen   = pack('N', strlen($s));
        return $slen.$s;
    }

    /// \copydoc Plop_HandlerInterface::handleError().
    public function handleError(
        Plop_RecordInterface    $record,
        Exception               $exception
    )
    {
        if ($this->_closeOnError) {
            $this->_close();
        }
        return parent::handleError($record, $exception);
    }

    /// \copydoc Plop_HandlerAbstract::_emit().
    protected function _emit(Plop_RecordInterface $record)
    {
        try {
            $s = $this->_makePickle($record);
            $this->_send($s);
        }
        catch (Exception $e) {
            $this->handleError($record, $e);
        }
    }

    /**
     * Close the socket associated with this handler.
     *
     * \return
     *      This method does not return any value.
     */
    protected function _close()
    {
        if ($this->_socket) {
            fclose($this->_socket);
            $this->_socket = FALSE;
        }
    }
}

