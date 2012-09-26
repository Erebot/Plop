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

class       Plop_Record
implements  Plop_RecordInterface
{
    protected $_dict;

    public function __construct(
        $name,
        $level,
        $pathname,
        $lineno,
        $msg,
        $args,
        $exception,
        $func = NULL
    )
    {
        static $pid = NULL;
        if ($pid === NULL)
            $pid = getmypid();

        $logging    = Plop::getInstance();
        $ct         = explode(" ", microtime(FALSE));
        $msecs      = (int) substr($ct[0]."000000", 2);
        /// @FIXME: There must be a better way to do this!
        $date       = new DateTime("@".$ct[1], new DateTimeZone("UTC"));
        $date       = new DateTime(
            sprintf(
                "%s.%s",
                $date->format("Y-m-d\\TH:i:s"),
                substr($ct[0], 2)
            ),
            new DateTimeZone("UTC")
        );
        $created    = ((float) $date->format("U.u"));
        $diff       = ($created - $logging->getCreationDate()) * 1000;

        if (isset($_SERVER['argv'][0]))
            $processName = basename($_SERVER['argv'][0]);
        else
            $processName = '-';

        $this->_dict['name']            = $name;
        $this->_dict['msg']             = $msg;
        $this->_dict['args']            = $args;
        $this->_dict['levelname']       = $logging->getLevelName($level);
        $this->_dict['levelno']         = $level;
        $this->_dict['pathname']        = $pathname;
        $this->_dict['filename']        = $pathname;
        $this->_dict['module']          = "Unknown module";
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

    public function getMessage()
    {
        return self::formatPercent($this->_dict['msg'], $this->_dict['args']);
    }

    static private function _pctPrefix($a)
    {
        return '%('.$a.')';
    }

    static private function _increment($a)
    {
        return '%'.($a + 1).'$';
    }

    static public function formatPercent($msg, $args)
    {
        if ($args === NULL || (is_array($args) && !count($args)))
            return $msg;

        if (!is_array($args))
            return sprintf($msg, $args);

        preg_match_all('/(?<!%)(?:%%)*%\\(([^\\)]*)\\)/', $msg, $matches);
        $args += array_combine(
            $matches[1],
            array_fill(0, count($matches[1]), NULL)
        );

        // Mapping = array(name => index)
        $keys       = array_keys($args);
        $mapping    = array_flip($keys);
        $keys       = array_map(array('self', '_pctPrefix'), $keys);
        $values     = array_map(array('self', '_increment'), $mapping);
        $mapping    = array_combine($keys, $values);
        $msg        = strtr($msg, $mapping);
        return vsprintf($msg, array_values($args));
    }

    public function offsetGet($offset)
    {
        return $this->_dict[$offset];
    }

    public function offsetSet($offset, $value)
    {
        return $this->_dict[$offset] = $value;
    }

    public function offsetExists($offset)
    {
        return isset($this->_dict[$offset]);
    }

    public function offsetUnset($offset)
    {
        unset($this->_dict[$offset]);
    }

    public function asDict()
    {
        return $this->_dict;
    }
}

