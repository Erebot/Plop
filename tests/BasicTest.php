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

require_once(
    dirname(__FILE__) .
    DIRECTORY_SEPARATOR . 'testenv' .
    DIRECTORY_SEPARATOR . 'bootstrap.php'
);

class LevelCheckingTest
extends PHPUnit_Extensions_OutputTestCase
{
    public function setUp()
    {
        $rootLogger = new Plop_RootLogger(Plop::WARNING);
        Plop_Logger::$root     = $rootLogger;
        Plop_Logger::$manager  = new Plop_Manager($rootLogger);
        $this->logging =& Plop::getInstance();
        $this->logging->basicConfig(array(
            'stream'    => fopen("php://output", "a+"),
            'level'     => Plop::INFO,
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
            array(Plop::DEBUG,      'This is a debug message'),
            array(Plop::INFO,       'This is an info message'),
            array(Plop::WARNING,    'This is a warning message'),
            array(Plop::ERROR,      'This is an error message'),
            array(Plop::CRITICAL,   'This is a critical error message'),
        );

        foreach ($messages as $tuple) {
            list($level, $msg) = $tuple;
            $this->logging->log($level, $msg);
        }

        $expected = array(
            'INFO:root:This is an info message',
            'WARNING:root:This is a warning message',
            'ERROR:root:This is an error message',
            'CRITICAL:root:This is a critical error message',
            ''
        );
        $expected = implode(PHP_EOL, $expected);
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

        $expected = "WARNING:package1/module1:$msg1".PHP_EOL.
                    "WARNING:package2/module2:$msg2".PHP_EOL;
        $this->expectOutputString($expected);
    }
}

