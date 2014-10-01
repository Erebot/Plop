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

namespace Plop;

/**
 *  \brief
 *      A class that stores a log record.
 *
 *  A log records contains everything that is related
 *  to a specific log, like what time it was emitted at,
 *  the level of the log, the file that emitted it, etc.
 */
class Record implements \Plop\RecordInterface
{
    /// Array of properties for the log record.
    protected $dict;

    /**
     * Construct a new log record.
     *
     * \param string $loggerNamespace
     *      Namespace associated with the logger that captured the log.
     *
     * \param string $loggerClass
     *      Class associated with the logger that captured the log.
     *
     * \param string $loggerMethod
     *      Method associated with the logger that captured the log.
     *
     * \param int $level
     *      The level of the log.
     *
     * \param string $pathname
     *      The name of the file where the log was emitted.
     *
     * \param int $lineno
     *      The line number where the log was emitted inside
     *      the file indicated by the \a $pathname parameter.
     *
     * \param string $msg
     *      The message to log.
     *
     * \param array $args
     *      Additional arguments for the log message.
     *
     * \param Plop::InterpolatorInterface $interpolator
     *      Interpolator to use during record formatting.
     *
     * \param null|Exception $exception
     *      An exception whose backtrace will be merged
     *      into the log message.
     */
    public function __construct(
        $loggerNamespace,
        $loggerClass,
        $loggerMethod,
        $level,
        $pathname,
        $lineno,
        $msg,
        array $args,
        \Plop\InterpolatorInterface $interpolator,
        \Exception $exception = null
    ) {
        static $pid = null;
        if ($pid === null) {
            $pid = getmypid();
        }

        $this->interpolator = $interpolator;
        $logging    = \Plop\Plop::getInstance();
        $ct         = explode(' ', microtime(false));
        $msecs      = (int) substr($ct[0] . '000000', 2);
        /// @FIXME: There must be a better way to do this!
        $date       = new \DateTime('@' . $ct[1], new \DateTimeZone('UTC'));
        $date       = new \DateTime(
            sprintf(
                '%s.%s',
                $date->format('Y-m-d\\TH:i:s'),
                substr($ct[0], 2)
            ),
            new \DateTimeZone('UTC')
        );
        $created        = ((float) $date->format('U.u'));
        $diff           = ($created - $logging->getCreationDate()) * 1000;
        if (isset($_SERVER['argv'][0])) {
            $processName = basename($_SERVER['argv'][0]);
        } else {
            $processName = '-';
        }
        // Represent the date using the local timezone (if configured).
        $date->setTimeZone(new \DateTimeZone(@date_default_timezone_get()));

        $this->dict['loggerNamespace']  = $loggerNamespace;
        $this->dict['loggerClass']      = $loggerClass;
        $this->dict['loggerMethod']     = $loggerMethod;
        $this->dict['msg']              = $msg;
        $this->dict['args']             = $args;
        $this->dict['levelname']        = $logging->getLevelName($level);
        $this->dict['levelno']          = $level;
        $this->dict['pathname']         = $pathname;
        $this->dict['filename']         = $pathname;
        $this->dict['module']           = 'Unknown module';
        $this->dict['exc_info']         = $exception;
        $this->dict['exc_text']         = null;
        $this->dict['lineno']           = $lineno;
        /// @FIXME: funcName should be != from $loggerMethod!
        $this->dict['funcName']         = $loggerMethod;
        $this->dict['msecs']            = $msecs;
        $this->dict['created']          = $created;
        $this->dict['createdDate']      = $date;
        $this->dict['relativeCreated']  = $diff;
        $this->dict['threadId']         = null;
        $this->dict['threadCreatorId']  = null;
        $this->dict['process']          = $pid;
        $this->dict['processName']      = $processName;
        $this->dict['hostname']         = php_uname('n');
    }

    /// \copydoc Plop::RecordInterface::getInterpolator().
    public function getInterpolator()
    {
        return $this->interpolator;
    }

    /// \copydoc Plop::RecordInterface::setInterpolator().
    public function setInterpolator(\Plop\InterpolatorInterface $interpolator)
    {
        $this->interpolator = $interpolator;
        return $this;
    }

    /// \copydoc Plop::RecordInterface::getMessage().
    public function getMessage()
    {
        return $this->interpolator->interpolate(
            $this->dict['msg'],
            $this->dict['args']
        );
    }

    /**
     * Return the value for one of the properties
     * of the log record.
     *
     * \param string $offset
     *      The name of the property to return.
     *      The default properties include:
     *      -   args
     *      -   created
     *      -   createdDate
     *      -   exc_info
     *      -   exc_text
     *      -   filename
     *      -   funcName
     *      -   hostname
     *      -   levelname
     *      -   levelno
     *      -   lineno
     *      -   loggerClass
     *      -   loggerFile
     *      -   loggerMethod
     *      -   module
     *      -   msecs
     *      -   msg
     *      -   pathname
     *      -   process
     *      -   processName
     *      -   relativeCreated
     *      -   threadId
     *      -   threadCreatorId
     *
     *      Additional properties may be available.
     *
     * \retval mixed
     *      The value for that property.
     */
    public function offsetGet($offset)
    {
        return $this->dict[$offset];
    }

    /**
     * Add/set a new value for a property
     * of this log.
     *
     * \param string $offset
     *      The name of the property to add/set.
     *
     * \param mixed $value
     *      The new value for that property.
     *
     * \see
     *      Plop::Record::offsetGet() for the names
     *      of all default properties.
     */
    public function offsetSet($offset, $value)
    {
        return $this->dict[$offset] = $value;
    }

    /**
     * Test whether a given property exists for
     * this log record.
     *
     * \param string $offset
     *      The name of the property whose existence
     *      must be checked.
     *
     * \retval bool
     *      \a true if the given property exists
     *      for this log record, \a false otherwise.
     */
    public function offsetExists($offset)
    {
        return isset($this->dict[$offset]);
    }

    /**
     * Remove a property from this log record.
     *
     * \param string $offset
     *      The name of the property to remove.
     */
    public function offsetUnset($offset)
    {
        unset($this->dict[$offset]);
    }

    /// \copydoc Plop::RecordInterface::asArray().
    public function asArray()
    {
        return $this->dict;
    }

    /**
     * Serialize this log record.
     *
     * \retval string
     *      Serialized representation of this record.
     */
    public function serialize()
    {
        return serialize(array($this->dict, $this->interpolator));
    }

    /**
     * Reconstruct a log record from
     * its serialized representation.
     *
     * \param string $data
     *      Serialized representation of the record.
     *
     * \return
     *      This method does not return any value.
     */
    public function unserialize($data)
    {
        $data = unserialize($data);
        $this->dict = $data[0];
        $this->setInterpolator($data[1]);
    }
}
