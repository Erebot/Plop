<?php

require_once('PHPUnit/Framework.php');
require_once('PHPUnit/Extensions/OutputTestCase.php');
include_once(dirname(dirname(__FILE__)).'/src/logging.php');

class LevelCheckingTest
extends PHPUnit_Extensions_OutputTestCase
{
    public function setUp()
    {
        $this->logging =& ErebotLogging::getInstance();
        $this->logging->basicConfig(array(
            'stream'    => fopen("php://output", "a+"),
            'level'     => EREBOT_LOG_INFO,
        ));
    }

    public function tearDown()
    {
        $root = $this->logging->getLogger();
        $root->handlers = array();
    }

    public function testLevelChecking()
    {
        $messages = array(
            array(EREBOT_LOG_DEBUG,     'This is a debug message'),
            array(EREBOT_LOG_INFO,      'This is an info message'),
            array(EREBOT_LOG_WARNING,   'This is a warning message'),
            array(EREBOT_LOG_ERROR,     'This is an error message'),
            array(EREBOT_LOG_CRITICAL,  'This is a critical error message'),
        );

        foreach ($messages as $tuple) {
            list($level, $msg) = $tuple;
            $this->logging->log($level, $msg);
        }

        $expected =<<<EXPECTED
INFO:root:This is an info message
WARNING:root:This is a warning message
ERROR:root:This is an error message
CRITICAL:root:This is a critical error message

EXPECTED;
        $this->expectOutputString($expected);
    }

    public function testMultipleModules()
    {
        $logger1 = $this->logging->getLogger('package1/module1');
        $logger2 = $this->logging->getLogger('package2/module2');

        $msg1 = "This message comes from one module";
        $msg2 = "And this message comes from another module";

        $logger1->warning($msg1);
        $logger2->warning($msg2);

        $expected = "WARNING:package1/module1:$msg1\n".
                    "WARNING:package2/module2:$msg2\n";
        $this->expectOutputString($expected);
    }
}

?>
