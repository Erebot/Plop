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

class Plop_Record
{
    public $dict;

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
        $parsed     = strptime($ct[1], "%s");
        $date       = new DateTime(
            sprintf(
                "%04d-%02d-%02dT%02d:%02d:%02d.%s",
                $parsed["tm_year"] + 1900,
                $parsed["tm_mon"] + 1,
                $parsed["tm_mday"],
                $parsed["tm_hour"],
                $parsed["tm_min"],
                $parsed["tm_sec"],
                substr($ct[0], 2)
            ),
            new DateTimeZone("UTC")
        );
        $created    = ((float) $date->format("U.u"));
        $diff       = ($created - $logging->created) * 1000;

        if (isset($_SERVER['argv'][0]))
            $processName = basename($_SERVER['argv'][0]);
        else
            $processName = '-';

        $this->dict['name']             = $name;
        $this->dict['msg']              = $msg;
        $this->dict['args']             = $args;
        $this->dict['levelname']        = $logging->getLevelName($level);
        $this->dict['levelno']          = $level;
        $this->dict['pathname']         = $pathname;
        $this->dict['filename']         = $pathname;
        $this->dict['module']           = "Unknown module";
        $this->dict['exc_info']         = $exception;
        $this->dict['exc_text']         = NULL;
        $this->dict['lineno']           = $lineno;
        $this->dict['funcName']         = $func;
        $this->dict['msecs']            = $msecs;
        $this->dict['created']          = $created;
        $this->dict['createdDate']      = $date;
        $this->dict['relativeCreated']  = $diff;
        $this->dict['thread']           = NULL;
        $this->dict['threadName']       = NULL;
        $this->dict['process']          = $pid;
        $this->dict['processName']      = $processName;
        $this->dict['hostname']         = php_uname('n');
    }

    public function getMessage()
    {
        return $this->formatPercent($this->dict['msg'], $this->dict['args']);
    }

    public function formatPercent($msg, $args)
    {
        if ($args === NULL || (is_array($args) && !count($args)))
            return $msg;

        if (!is_array($args))
            return sprintf($msg, $args);

        // Mapping = array(name => index)
        $keys       = array_keys($args);
        $mapping    = array_flip($keys);
        $pctPrefix  = create_function('$a', 'return "%(".$a.")";');
        $increment  = create_function('$a', 'return "%".($a + 1)."\\$";');
        $keys       = array_map($pctPrefix, $keys);
        $values     = array_map($increment, $mapping);
        $mapping    = array_combine($keys, $values);
        $msg        = strtr($msg, $mapping);
        return vsprintf($msg, array_values($args));
    }
}

