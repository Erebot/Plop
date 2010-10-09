<?php

abstract class AErebotLoggingConfig
{
    protected $logging;

    public function __construct(ErebotLogging &$logging, $fname)
    {
        $this->logging  =& $logging;
        $this->cp       =  $this->getConfigParserData($fname);
    }

    abstract protected function getConfigParserData($fname);

    protected function createHandlerInstance($klass, &$args)
    {
        $pos = strrchr($klass, ':');
        if ($pos === FALSE) {
            $cls    = $klass;
            $fname  = $klass;
            $prefix = 'ErebotLogging';
            $plen   = strlen($prefix);
            if (!strncmp($fname, $prefix, $plen))
                $fname = substr($fname, $plen);
            else
                $cls = $prefix.$cls;

            $fname  = dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.
                            'handlers'.DIRECTORY_SEPARATOR.$fname.'.php';
        }
        else {
            $cls    = substr($klass, $pos + 1);
            $fname  = substr($klass, 0, $pos);
            $first  = substr($fname, 0, 1);
            $abs    = FALSE;
            if ($first == DIRECTORY_SEPARATOR)
                $abs = TRUE;
            else if (!strncasecmp(PHP_OS, 'Win', 3) && $first == "/")
                $abs = TRUE;
            if (strpos($fname, '.') === FALSE)
                $fname .= '.php';
            if (!$abs)
                $fname = dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.
                                'handlers'.DIRECTORY_SEPARATOR.$fname;
        }
        if (!file_exists($fname))
            throw new Exception(sprintf('No such handler (%s)', $fname));
        include_once($fname);
        if (!class_exists($cls) ||
            !is_subclass_of($cls, 'ErebotLoggingHandler'))
            throw new Exception(sprintf('No such class (%s)', $cls));

        // call_user_func_array doesn't work with constructors.
        // We use the reflection API instead, which allows a ctor
        // to be called with a variable number of args.
        $reflector = new ReflectionClass($cls);
        return $reflector->newInstanceArgs($args);
    }

    public function doWork()
    {
        $formatters = $this->createFormatters();
        $handlers = $this->installHandlers($formatters);
        $this->installLoggers($handlers);
    }

    abstract protected function createFormatters();
    abstract protected function installHandlers($formatters);
    abstract protected function installLoggers($handlers);
}

?>
