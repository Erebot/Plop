<?php

class   ErebotLoggingConfigXML
extends AErebotLoggingConfig
{
    protected function getConfigParserData($fname)
    {
        if (is_string($fname))
            return simplexml_load_file($fname);
        if ($fname instanceof SimpleXMLElement)
            return $fname;
        if ($fname instanceof DOMNode)
            return simplexml_import_dom($fname);
        throw new Exception('Invalid filename');
    }

    protected function createFormatters()
    {
        $formatters = array();
        if (!isset($this->cp->children(EREBOT_LOG_XMLNS)->formatters[0]))
            return $formatters;

        foreach ($this->cp->children(EREBOT_LOG_XMLNS)->formatters[0]
                    ->children(EREBOT_LOG_XMLNS)->formatter as $formatter) {
            $name = (string) $formatter->attributes('')->name;
            if (isset($formatter->children(EREBOT_LOG_XMLNS)->format[0]))
                $fs = (string) $formatter->children(EREBOT_LOG_XMLNS)->format;
            else
                $fs = NULL;
            if (isset($formatter->children(EREBOT_LOG_XMLNS)->datefmt[0]))
                $dfs = (string) $formatter->children(EREBOT_LOG_XMLNS)->datefmt;
            else
                $dfs = NULL;
            $c = 'ErebotLoggingFormatter';
            if (isset($formatter->children(EREBOT_LOG_XMLNS)->{'class'}[0]))
                $c = (string) $formatter->children(EREBOT_LOG_XMLNS)->{'class'};
            $formatters[$name] = new $c($fs, $dfs);
        }
        return $formatters;
    }

    protected function installHandlers($formatters)
    {
        $handlers = array();
        if (!isset($this->cp->children(EREBOT_LOG_XMLNS)->handlers[0]))
            return $handlers;
    
        $fixups = array();
        foreach ($this->cp->children(EREBOT_LOG_XMLNS)->handlers[0]
                    ->children(EREBOT_LOG_XMLNS)->handler as $handler) {
            $name   = (string) $handler->attributes('')->name;
            $klass  = (string) $handler->children(EREBOT_LOG_XMLNS)->{'class'};
            $args   = array(); # @TODO: parse args from config file.
            $h = $this->createHandlerInstance($klass, $args);
            if (isset($handler->children(EREBOT_LOG_XMLNS)->level[0])) {
                $level = (string) $handler->children(EREBOT_LOG_XMLNS)->level;
                if (is_numeric($level))
                    $level = (int) $level;
                else
                    $level = $this->logging->getLevelName($level);
                $h->setLevel($level);
            }
            if (isset($handler->children(EREBOT_LOG_XMLNS)->formatter[0])) {
                $fmt = (string) $handler->children(EREBOT_LOG_XMLNS)->formatter;
                $h->setFormatter($formatters[$fmt]);
            }
            if (isset($handler->children(EREBOT_LOG_XMLNS)->target[0]))
                $fixups[$name] = (string) $handler->children(EREBOT_LOG_XMLNS)->target[0];
            $handlers[$name] = $h;
        }
        foreach ($fixups as $n => $t)
            $handlers[$n]->setTarget($handlers[$t]);
        return $handlers;
    }

    protected function installLoggers($handlers)
    {
        if (!isset($this->cp->children(EREBOT_LOG_XMLNS)->loggers[0]))
            return;

        $root       = $this->logging->getLogger();
        $existing   = array_keys($root::$manager->loggerDict);
        foreach ($this->cp->children(EREBOT_LOG_XMLNS)->loggers[0]
                    ->children(EREBOT_LOG_XMLNS)->logger as $logger) {
            $name = (string) $logger->attributes('')->name;
            if ($name == "root") {
                $xrl    =&  $logger;
                $log    =&  $root;
                if (isset($xrl->children(EREBOT_LOG_XMLNS)->level[0])) {
                    $level = (string) $xrl->children(EREBOT_LOG_XMLNS)->level;
                    if (is_numeric($level))
                        $level = (int) $level;
                    else
                        $level = $this->logging->getLevelName($level);
                    $log->setLevel($level);
                }
                foreach ($root->handlers as $h)
                    $root->removeHandler($h);
                if (isset($xrl->children(EREBOT_LOG_XMLNS)->handlers[0])) {
                    foreach ($xrl->children(EREBOT_LOG_XMLNS)->handlers[0]
                                ->children(EREBOT_LOG_XMLNS)->handler as $hand) {
                        $hname = trim((string) $hand);
                        $log->addHandler($handlers[$hname]);
                    }
                }
            }

            $qn = (string) $logger->children(EREBOT_LOG_XMLNS)->qualname;
            if (isset($logger->children(EREBOT_LOG_XMLNS)->propagate[0]))
                $propagate = (int) ((string) $logger->children(EREBOT_LOG_XMLNS)->propagate);
            else
                $propagate = 1;
            $qnLogger = $this->logging->getLogger($qn);
            $key = array_search($qn, $existing, TRUE);
            if ($key !== FALSE)
                unset($existing[$key]);
            if (isset($logger->children(EREBOT_LOG_XMLNS)->level[0])) {
                $level = (string) $logger->children(EREBOT_LOG_XMLNS)->level;
                if (is_numeric($level))
                    $level = (int) $level;
                else
                    $level = $this->logging->getLevelName($level);
                $qnLogger->setLevel($level);
            }
            foreach ($qnLogger->handlers as &$h)
                $qnLogger->removeHandler($h);
            unset($h);
            $qnLogger->propagate  = $propagate;
            $qnLogger->disabled   = 0;
            foreach ($logger->children(EREBOT_LOG_XMLNS)->handlers[0]
                            ->children(EREBOT_LOG_XMLNS)->handler as $hand) {
                $hname = trim((string) $hand);
                $qnLogger->addHandler($handlers[$hname]);
            }
        }
        foreach ($existing as $log)
            $root->manager->loggerDict[$log]->disabled = 1;
    }
}

?>
