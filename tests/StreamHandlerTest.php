<?php

require_once('PHPUnit/Framework.php');

use PEAR2\Plop;

class StreamHandlerTest
extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        Plop\Logger::$root     = new Plop\RootLogger(Plop\Plop::WARNING);
        Plop\Logger::$manager  = new Plop\Manager(Plop\Logger::$root);
        $this->tempfile = tmpfile();
        $this->logging =& Plop\Plop::getInstance();
        $this->logging->basicConfig(array(
            'stream'    => $this->tempfile,
            'level'     => Plop\Plop::DEBUG,
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

