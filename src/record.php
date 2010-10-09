<?php

class ErebotLoggingRecord
{
    public $dict;

    public function __construct($name, $level, $pathname, $lineno, $msg, $args, $exc_info, $func = NULL)
    {
        $ct         = microtime(TRUE);
        $logging    = ErebotLogging::getInstance();

        if (isset($_SERVER['argv'][0]))
            $processName = basename($_SERVER['argv'][0]);
        else
            $processName = '???';

        $this->dict['name']             = $name;
        $this->dict['msg']              = $msg;
        $this->dict['args']             = $args;
        $this->dict['levelname']        = $logging->getLevelName($level);
        $this->dict['levelno']          = $level;
        $this->dict['pathname']         = $pathname;
        $this->dict['filename']         = $pathname;
        $this->dict['module']           = "Unknown module";
        $this->dict['exc_info']         = $exc_info;
        $this->dict['exc_text']         = NULL;
        $this->dict['lineno']           = $lineno;
        $this->dict['funcName']         = $func;
        $this->dict['created']          = $ct;
        $this->dict['msecs']            = ($ct - ((int) $ct)) * 1000;
        $this->dict['relativeCreated']  = ($ct - $logging->created) * 1000;
        $this->dict['thread']           = NULL;
        $this->dict['threadName']       = NULL;
        $this->dict['process']          = getmypid();
        $this->dict['processName']      = $processName;
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

?>
