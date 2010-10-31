<?php

namespace PEAR2\Plop\Config\Format;
use PEAR2\Plop\Config\AbstractParser;

class   INI
extends AbstractParser
{
    protected function getConfigParserData($fname)
    {
        if (is_array($fname))
            return $fname;
        if (is_string($fname))
            return parse_ini_file($fname, TRUE);
        throw new Exception('Invalid filename');
    }

    protected function createFormatters()
    {
        $formatters = array();
        if (!isset($this->cp['formatters']['keys']))
            return $formatters;

        $flist = explode(',', $this->cp['formatters']['keys']);
        if (!count($flist))
            return $formatters;

        foreach ($flist as $form) {
            $sectname = "formatter_".trim($form);
            $opts = $this->cp[$sectname];
            if (isset($opts['format']))
                $fs = $opts['format'];
            else
                $fs = NULL;
            if (isset($opts['datefmt']))
                $dfs = $opts['datefmt'];
            else
                $dfs = NULL;
            $c = '\\PEAR2\\Plop\\Formatter';
            if (isset($opts['class']))
                $c = $opts['class'];
            $formatters[$form] = new $c($fs, $dfs);;
        }
        return $formatters;
    }

    protected function installHandlers($formatters)
    {
        $handlers = array();
        if (!isset($this->cp['handlers']['keys']))
            return $handlers;

        $hlist = explode(',', $this->cp['handlers']['keys']);
        if (!count($hlist))
            return $handlers;

        $fixups = array();
        foreach ($hlist as $hand) {
            $sectname = "handler_".trim($hand);
            $opts = $this->cp[$sectname];
            $args = array();
            if (isset($this->cp[$sectname]['args']))
                $args = $this->cp[$sectname]['args'];
            $h = $this->createHandlerInstance($this->cp[$sectname]['class'], $args);
            if (isset($opts['level'])) {
                $level = $opts['level'];
                if (is_string($level))
                    $level = $this->_logging->getLevelName($level);
                $h->setLevel($level);
            }
            if (isset($opts['formatter'])) {
                $fmt = $opts['formatter'];
                $h->setFormatter($formatters[$fmt]);
            }
            if (isset($opts['target']))
                $fixups[$hand] = $opts['target'];
            $handlers[$hand] = $h;
        }

        foreach ($fixups as $n => $t)
            $handlers[$n]->setTarget($handlers[$t]);
        return $handlers;
    }

    protected function installLoggers($handlers)
    {
        if (!isset($this->cp['loggers']['keys']))
            return;

        $llist = array_map('trim', explode(',', $this->cp['loggers']['keys']));
        $key = array_search('root', $llist);
        if ($key !== FALSE)
            unset($llist[$key]);

        $sectname   =   "logger_root";
        $root       =   $this->_logging->getLogger();
        $log        =&  $root;

        if (isset($this->cp[$sectname])) {
            $opts = $this->cp[$sectname];
            if (isset($opts['level'])) {
                $level = $opts['level'];
                if (is_string($level))
                    $level = $this->_logging->getLevelName($level);
                $log->setLevel($level);
            }
            foreach ($root->handlers as $h)
                $root->removeHandler($h);

            if (isset($opts['handlers'])) {
                $hlist = explode(',', $opts['handlers']);
                foreach ($hlist as $hand) {
                    $log->addHandler($handlers[$hand]);
                }
            }
        }

        $existing   = array_keys($root::$manager->loggerDict);
        foreach ($llist as $log) {
            $sectname = "logger_".$log;
            $qn = $this->cp[$sectname]['qualname'];
            $opts = $this->cp[$sectname];
            if (isset($opts['propagate']))
                $propagate = (int) $opts['propagate'];
            else
                $propagate = 1;
            $logger = $this->_logging->getLogger($qn);
            if (isset($existing[$qn])) {
                $key = array_search($qn, $existing);
                if ($key !== FALSE)
                    unset($existing[$key]);
            }
            if (isset($opts['level'])) {
                $level = $opts['level'];
                if (is_string($level))
                    $level = $this->_logging->getLevelName($level);
                $logger->setLevel($level);
            }
            foreach ($logger->handlers as &$h)
                $logger->removeHandler($h);
            unset($h);
            $logger->propagate = $propagate;
            $logger->disabled = 0;
            $hlist = explode(',', $opts['handlers']);
            foreach ($hlist as $hand)
                $logger->addHandler($handlers[trim($hand)]);
        }
        foreach ($existing as $log)
            $root->manager->loggerDict[$log]->disabled = 1;
    }
}

