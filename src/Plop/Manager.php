<?php

class Plop_Manager
{
    public $root;
    public $disable;
    public $emittedNoHandlerWarning;
    public $loggerDict;

    public function __construct(Plop_Logger &$rootnode)
    {
        $this->root                     =&  $rootnode;
        $this->disable                  =   0;
        $this->emittedNoHandlerWarning  =   0;
        $this->loggerDict               =   array();
    }

    public function getLogger($name)
    {
        $logging =& Plop::getInstance();
        $cls = $logging->getLoggerClass();
        $rv = NULL;
        if (isset($this->loggerDict[$name])) {
            $rv =& $this->loggerDict[$name];
            if ($rv instanceof Plop_PlaceHolder) {
                $ph =   $rv;
                $rv =   new $cls($name);
                $this->loggerDict[$name] =& $rv;
                $this->fixupChildren($ph, $rv);
                $this->fixupParents($rv);
            }
        }
        else {
            $rv = new $cls($name);
            $this->loggerDict[$name] =& $rv;
            $this->fixupParents($rv);
        }
        return $rv;
    }

    protected function fixupParents(Plop_Logger &$alogger)
    {
        $name = $alogger->name;
        $i = strrpos($name, DIRECTORY_SEPARATOR);
        $rv = NULL;
        while ($i && $rv === NULL) {
            $substr = substr($name, 0, $i);
            if (!isset($this->loggerDict[$substr]))
                $this->loggerDict[$substr] = new Plop_PlaceHolder($alogger);
            else {
                $obj =& $this->loggerDict[$substr];
                if ($obj instanceof Plop_Logger)
                    $rv =& $obj;
                else {
                    assert($obj instanceof Plop_PlaceHolder);
                    $obj->append($alogger);
                }
            }
            $i = strrpos(substr($name, 0, $i - 1), DIRECTORY_SEPARATOR);
        }
        if (!$rv)
            $rv =& $this->root;
        $alogger->parent =& $rv;
    }

    protected function fixupChildren($ph, Plop_Logger &$alogger)
    {
        $name       = $alogger->name;
        $namelen    = strlen($name);
        foreach ($ph->loggerMap as &$c) {
            if (substr($c->parent->name, 0, $namelen) != $name) {
                $alogger->parent    =& $c->parent;
                $c->parent          =& $alogger;
            }
        }
        unset($c);
    }
}

