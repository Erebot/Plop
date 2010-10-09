<?php

require_once('PHPUnit/Framework.php');
include_once(dirname(dirname(__FILE__)).'/src/logging.php');

class StreamHandlerTest
extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->tempfile = tmpfile();
        $this->logging =& ErebotLogging::getInstance();
        $this->logging->basicConfig(array(
            'stream'    => $this->tempfile,
            'level'     => EREBOT_LOG_DEBUG,
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

?>
