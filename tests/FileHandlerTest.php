<?php

require_once('PHPUnit/Framework.php');
require_once(dirname(__FILE__).'/../src/Plop/Plop.php');

class FileHandlerTest
extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        Plop_Logger::$root     = new Plop_RootLogger(PLOP_LEVEL_WARNING);
        Plop_Logger::$manager  = new Plop_Manager(Plop_Logger::$root);
        $this->tempfile = tempnam(sys_get_temp_dir(), 'PLOP');
        $this->logging =& Plop::getInstance();
        $this->logging->basicConfig(array(
            'filename'  => $this->tempfile,
            'level'     => PLOP_LEVEL_DEBUG,
        ));
    }

    public function tearDown()
    {
        $root = $this->logging->getLogger();
        $root->handlers = array();
        @unlink($this->tempfile);
    }

    public function testBasicFileHandler()
    {
        $message = 'This message should go to the log file';
        $this->logging->debug($message);
        $content = file_get_contents($this->tempfile);
        $expected = "DEBUG:root:$message\n";
        $this->assertEquals($expected, $content);
    }
}

