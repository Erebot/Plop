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
 *      A class that stores a log record.
 *
 *  A log records contains everything that is related
 *  to a specific log, like what time it was emitted at,
 *  the level of the log, the file that emitted it, etc.
 */
class       Plop_Record
implements  Plop_RecordInterface
{
    /// Array of properties for the log record.
    protected $_dict;

    /**
     * Construct a new log record.
     *
     * \param string $loggerFile
     *      File associated with the logger that captured
     *      the log.
     *
     * \param string $loggerClass
     *      Class associated with the logger that captured
     *      the log.
     *
     * \param string $loggerMethod
     *      Method associated with the logger that captured
     *      the log.
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
     * \param NULL|Exception $exception
     *      An exception whose backtrace will be merged
     *      into the log message.
     *
     * \param NULL|string $func
     *      The name of the function/method inside \a $pathname
     *      where the log was emitted.
     */
    public function __construct(
                    $loggerFile,
                    $loggerClass,
                    $loggerMethod,
                    $level,
                    $pathname,
                    $lineno,
                    $msg,
        array       $args,
        Exception   $exception  = NULL,
                    $func       = NULL
    )
    {
        static $pid = NULL;
        if ($pid === NULL)
            $pid = getmypid();

        $logging    = Plop::getInstance();
        $ct         = explode(' ', microtime(FALSE));
        $msecs      = (int) substr($ct[0] . '000000', 2);
        /// @FIXME: There must be a better way to do this!
        $date       = new DateTime('@' . $ct[1], new DateTimeZone('UTC'));
        $date       = new DateTime(
            sprintf(
                '%s.%s',
                $date->format('Y-m-d\\TH:i:s'),
                substr($ct[0], 2)
            ),
            new DateTimeZone('UTC')
        );
        $created        = ((float) $date->format('U.u'));
        $diff           = ($created - $logging->getCreationDate()) * 1000;
        if (isset($_SERVER['argv'][0])) {
            $processName = basename($_SERVER['argv'][0]);
        }
        else {
            $processName = '-';
        }

        $this->_dict['loggerFile']      = $loggerFile;
        $this->_dict['loggerClass']     = $loggerClass;
        $this->_dict['loggerMethod']    = $loggerMethod;
        $this->_dict['msg']             = $msg;
        $this->_dict['args']            = $args;
        $this->_dict['levelname']       = $logging->getLevelName($level);
        $this->_dict['levelno']         = $level;
        $this->_dict['pathname']        = $pathname;
        $this->_dict['filename']        = $pathname;
        $this->_dict['module']          = 'Unknown module';
        $this->_dict['exc_info']        = $exception;
        $this->_dict['exc_text']        = NULL;
        $this->_dict['lineno']          = $lineno;
        $this->_dict['funcName']        = $func;
        $this->_dict['msecs']           = $msecs;
        $this->_dict['created']         = $created;
        $this->_dict['createdDate']     = $date;
        $this->_dict['relativeCreated'] = $diff;
        $this->_dict['thread']          = NULL;
        $this->_dict['threadName']      = NULL;
        $this->_dict['process']         = $pid;
        $this->_dict['processName']     = $processName;
        $this->_dict['hostname']        = php_uname('n');
    }

    /// \copydoc Plop_RecordInterface::getMessage().
    public function getMessage()
    {
        return self::formatPercent($this->_dict['msg'], $this->_dict['args']);
    }

    /**
     * Return a percent-prefixed variable.
     *
     * \param string $a
     *      Variable to work on.
     *
     * \retval string
     *      Percent-prefixed version of the variable name.
     *
     * @codeCoverageIgnore
     */
    static private function _pctPrefix($a)
    {
        return '%('.$a.')';
    }

    /**
     * Return an incremented and percent-prefixed variable.
     *
     * \param int $a
     *      Variable to work on.
     *
     * \retval string
     *      Incremented and percent-prefixed version
     *      of the variable.
     *
     * @codeCoverageIgnore
     */
    static private function _increment($a)
    {
        return '%'.($a + 1).'$';
    }

    /// \copydoc Plop_RecordInterface::formatPercent().
    static public function formatPercent($msg, array $args = array())
    {
        preg_match_all('/(?<!%)(?:%%)*%\\(([^\\)]*)\\)/', $msg, $matches);
        // Only define the variables if there are any.
        if (isset($matches[1][0])) {
            $args += array_combine(
                $matches[1],
                array_fill(0, count($matches[1]), NULL)
            );
        }

        if (!count($args))
            return $msg;

        // Mapping = array(name => index)
        $keys       = array_keys($args);
        $mapping    = array_flip($keys);
        $keys       = array_map(array('self', '_pctPrefix'), $keys);
        $values     = array_map(array('self', '_increment'), $mapping);
        $mapping    = array_combine($keys, $values);
        $msg        = strtr($msg, $mapping);
        return vsprintf($msg, array_values($args));
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
     *      -   thread
     *      -   threadName
     *
     *      Additional properties may be available.
     *
     * \retval mixed
     *      The value for that property.
     */
    public function offsetGet($offset)
    {
        return $this->_dict[$offset];
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
     *      Plop_Record::offsetGet() for the names
     *      of all default properties.
     */
    public function offsetSet($offset, $value)
    {
        return $this->_dict[$offset] = $value;
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
     *      \a TRUE if the given property exists
     *      for this log record, \a FALSE otherwise.
     */
    public function offsetExists($offset)
    {
        return isset($this->_dict[$offset]);
    }

    /**
     * Remove a property from this log record.
     *
     * \param string $offset
     *      The name of the property to remove.
     */
    public function offsetUnset($offset)
    {
        unset($this->_dict[$offset]);
    }

    /// \copydoc Plop_RecordInterface::asArray().
    public function asArray()
    {
        return $this->_dict;
    }

    /**
     * Serialize this log record.
     *
     * \retval string
     *      Serialized representation of this record.
     */
    public function serialize()
    {
        return serialize($this->_dict);
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
        $this->_dict = unserialize($data);
    }
}

