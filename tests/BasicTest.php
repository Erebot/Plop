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

require_once('PHPUnit/Framework.php');
require_once('PHPUnit/Extensions/OutputTestCase.php');

class LevelCheckingTest
extends PHPUnit_Extensions_OutputTestCase
{
    public function setUp()
    {
        Plop_Logger::$root     = new Plop_RootLogger(Plop_Plop::WARNING);
        Plop_Logger::$manager  = new Plop_Manager(Plop_Logger::$root);
        $this->logging =& Plop_Plop::getInstance();
        $this->logging->basicConfig(array(
            'stream'    => fopen("php://output", "a+"),
            'level'     => Plop_Plop::INFO,
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
            array(Plop_Plop::DEBUG,      'This is a debug message'),
            array(Plop_Plop::INFO,       'This is an info message'),
            array(Plop_Plop::WARNING,    'This is a warning message'),
            array(Plop_Plop::ERROR,      'This is an error message'),
            array(Plop_Plop::CRITICAL,   'This is a critical error message'),
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

