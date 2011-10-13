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

class   Plop_Config_Format_XML
extends Plop_Config_ParserAbstract
{
    const XMLNS = 'http://www.erebot.net/xmlns/logging';

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
        if (!isset($this->cp->children(self::XMLNS)->formatters[0]))
            return $formatters;

        foreach ($this->cp->children(self::XMLNS)->formatters[0]
                    ->children(self::XMLNS)->formatter as $formatter) {
            $name = (string) $formatter->attributes('')->name;
            if (isset($formatter->children(self::XMLNS)->format[0]))
                $fs = (string) $formatter->children(self::XMLNS)->format;
            else
                $fs = NULL;
            if (isset($formatter->children(self::XMLNS)->datefmt[0]))
                $dfs = (string) $formatter->children(self::XMLNS)->datefmt;
            else
                $dfs = NULL;
            $c = 'Plop_Formatter';
            if (isset($formatter->children(self::XMLNS)->{'class'}[0]))
                $c = (string) $formatter->children(self::XMLNS)->{'class'};
            $formatters[$name] = new $c($fs, $dfs);
        }
        return $formatters;
    }

    protected function installHandlers($formatters)
    {
        $handlers = array();
        if (!isset($this->cp->children(self::XMLNS)->handlers[0]))
            return $handlers;

        $fixups = array();
        foreach ($this->cp->children(self::XMLNS)->handlers[0]
                    ->children(self::XMLNS)->handler as $handler) {
            $name   = (string) $handler->attributes('')->name;
            $klass  = (string) $handler->children(self::XMLNS)->{'class'};
            $args   = array(); /// @TODO: parse args from config file.
            $h = $this->createHandlerInstance($klass, $args);
            if (isset($handler->children(self::XMLNS)->level[0])) {
                $level = (string) $handler->children(self::XMLNS)->level;
                if (is_numeric($level))
                    $level = (int) $level;
                else
                    $level = $this->_logging->getLevelName($level);
                $h->setLevel($level);
            }
            if (isset($handler->children(self::XMLNS)->formatter[0])) {
                $fmt = (string) $handler->children(self::XMLNS)->formatter;
                $h->setFormatter($formatters[$fmt]);
            }
            if (isset($handler->children(self::XMLNS)->target[0]))
                $fixups[$name] =
                    (string) $handler->children(self::XMLNS)->target[0];
            $handlers[$name] = $h;
        }
        foreach ($fixups as $n => $t)
            $handlers[$n]->setTarget($handlers[$t]);
        return $handlers;
    }

    protected function installLoggers($handlers, $disableExistingLoggers)
    {
        if (!isset($this->cp->children(self::XMLNS)->loggers[0]))
            return;

        $root           = $this->_logging->getLogger();
        $rootRefl       = new ReflectionObject($root);
        $manager        = $rootRefl->getStaticPropertyValue('manager');
        $existing       = array_keys($manager->loggerDict);
        sort($existing);
        $childLoggers   = array();
        foreach ($this->cp->children(self::XMLNS)->loggers[0]
                    ->children(self::XMLNS)->logger as $loggerXML) {
            $name = (string) $loggerXML->attributes('')->name;
            if ($name == "root") {
                $log    =&  $root;
                if (isset($loggerXML->children(self::XMLNS)->level[0])) {
                    $level = (string) $loggerXML->children(self::XMLNS)->level;
                    if (is_numeric($level))
                        $level = (int) $level;
                    else
                        $level = $this->_logging->getLevelName($level);
                    $log->setLevel($level);
                }
                foreach ($root->handlers as $h)
                    $root->removeHandler($h);
                if (isset($loggerXML->children(self::XMLNS)->handlers[0])) {
                    foreach ($loggerXML
                                ->children(self::XMLNS)->handlers[0]
                                ->children(self::XMLNS)->handler as $hand) {
                        $hname = trim((string) $hand);
                        $log->addHandler($handlers[$hname]);
                    }
                }
                continue;
            }

            $qn = (string) $loggerXML->children(self::XMLNS)->qualname;
            if (isset($loggerXML->children(self::XMLNS)->propagate[0]))
                $propagate = (int) (
                    (string) $loggerXML->children(self::XMLNS)->propagate
                );
            else
                $propagate = 1;
            $logger = $this->_logging->getLogger($qn);
            $key = array_search($qn, $existing, TRUE);
            if ($key !== FALSE) {
                $i = $key + 1;
                $prefixed = $qn . DIRECTORY_SEPARATOR;
                $pflen = strlen($prefixed);
                $numExisting = count($existing);
                while ($i < $numExisting &&
                    strncmp($existing[$i], $prefixed, $pflen))
                    $childLoggers[] = $existing[$i++];
                unset($existing[$key]);
            }
            if (isset($loggerXML->children(self::XMLNS)->level[0])) {
                $level = (string) $loggerXML->children(self::XMLNS)->level;
                if (is_numeric($level))
                    $level = (int) $level;
                else
                    $level = $this->_logging->getLevelName($level);
                $logger->setLevel($level);
            }
            foreach ($logger->handlers as &$h)
                $logger->removeHandler($h);
            unset($h);
            $logger->propagate  = $propagate;
            $logger->disabled   = 0;
            foreach ($loggerXML
                        ->children(self::XMLNS)->handlers[0]
                        ->children(self::XMLNS)->handler as $hand) {
                $hname = trim((string) $hand);
                $logger->addHandler($handlers[$hname]);
            }
        }

        foreach ($existing as $log) {
            $logger = $manager->loggerDict[$log];
            if (isset($childLoggers[$log])) {
                $logger->level      = Plop::NOTSET;
                $logger->handlers   = array();
                $logger->propagate  = 1;
            }
            else if ($disableExistingLoggers)
                $logger->disabled = 1;
        }
    }
}

