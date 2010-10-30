<?php

require_once('PHPUnit/Framework.php');
require_once(dirname(__FILE__).'/../src/Plop/Plop.php');

class StreamHandlerTest
extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        Plop_Logger::$root     = new Plop_RootLogger(PLOP_LEVEL_WARNING);
        Plop_Logger::$manager  = new Plop_Manager(Plop_Logger::$root);
        $this->tempfile = tmpfile();
        $this->logging =& Plop::getInstance();
        $this->logging->basicConfig(array(
            'stream'    => $this->tempfile,
            'level'     => PLOP_LEVEL_DEBUG,
        ));
    }

    public function tearDown()
    {
        $root = $this->logging->getLogger();
        $root->handlers = array();
        @fclose($this->tempfile);
    }

    public function testBasicStreamHandler()
    {
        $message = 'This message should go to the log file';
        $this->logging->debug($message);
        fflush($this->tempfile);
        rewind($this->tempfile);
        $content = stream_get_contents($this->tempfile);
        $expected = "DEBUG:root:$message\n";
        $this->assertEquals($expected, $content);
    }
}

