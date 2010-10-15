<?php

require_once('PHPUnit/Framework.php');

class FileHandlerTest
extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        PlopLogger::$root     = new PlopRootLogger(PLOP_LEVEL_WARNING);
        PlopLogger::$manager  = new PlopManager(PlopLogger::$root);
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

?>
