<?php
/*
    This file is part of Plop, a simple logging library for PHP.

    Copyright © 2010-2014 François Poirotte

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

use PHPUnit\Framework\TestCase;

require_once(__DIR__ . DIRECTORY_SEPARATOR . 'Stderr.php');
require_once(__DIR__ . DIRECTORY_SEPARATOR . 'Socket.php');

class   Plop_TestCase
extends TestCase
{
    protected $stderrExpectedRegex;
    protected $stderrExpectedString;
    protected $stderr;
    protected $stderrStream;

    public function setUp()
    {
        parent::setUp();

        $context = stream_context_create(
            array(
                'mock' => array(
                    'callback' => array($this, 'writeToStderr')
                )
            )
        );

        $this->_expectations        = array();
        $this->stderrExpectedRegex  = NULL;
        $this->stderrExpectedString = NULL;
        $this->stderr               = '';

        stream_wrapper_register('stderr', 'Plop_Testenv_Stderr');
        $this->stderrStream = fopen('stderr://', 'at', FALSE, $context);
        stream_wrapper_unregister('stderr');
    }

    public function tearDown()
    {
        @fclose($this->stderrStream);

        if ($this->stderrExpectedRegex !== NULL) {
            $this->assertRegExp($this->stderrExpectedRegex, $this->stderr);
            $this->stderrExpectedRegex = NULL;
        }

        else if ($this->stderrExpectedString !== NULL) {
            $this->assertEquals($this->stderrExpectedString, $this->stderr);
            $this->stderrExpectedString = NULL;
        }
        parent::tearDown();
    }

    public function writeToStderr($data)
    {
        $this->stderr .= $data;
    }

    public function expectStderrRegex($expectedRegex)
    {
        if ($this->stderrExpectedString !== NULL) {
            throw new PHPUnit_Framework_Exception;
        }

        if (is_string($expectedRegex) || is_null($expectedRegex)) {
            $this->stderrExpectedRegex = $expectedRegex;
        }
    }

    public function expectStderrString($expectedString)
    {
        if ($this->stderrExpectedRegex !== NULL) {
            throw new PHPUnit_Framework_Exception;
        }

        if (is_string($expectedString) || is_null($expectedString)) {
            $this->stderrExpectedString = $expectedString;
        }
    }

    protected function setExpectations($obj, $method, $inputs, $outputs, $regex = false)
    {
        $class = get_class($obj);
        $this->assertSame(count($inputs), count($outputs));
        $obj->expects($this->exactly(count($inputs)))
            ->method($method)
            ->will($this->returnCallback(array($this, 'matchCallback')));
        $this->_expectations[$class][$method] = array(
            'index'     => 0,
            'inputs'    => $inputs,
            'outputs'   => $outputs,
            'regex'     => $regex,
        );
    }

    public function matchCallback()
    {
        $bt     = debug_backtrace(FALSE);
        $class  = NULL;
        $method = NULL;
        for ($i = 0, $m = count($bt); $i < $m; $i++) {
            if (isset($bt[$i]['class']) &&
                isset($bt[$i]['function']) &&
                isset(
                    $this->_expectations[$bt[$i]['class']][$bt[$i]['function']]
                )) {
                    $class  = $bt[$i]['class'];
                    $method = $bt[$i]['function'];
            }
        }
        $this->assertNotNull($class);
        $this->assertNotNull($method);

        $args   = func_get_args();
        $index  = $this->_expectations[$class][$method]['index']++;
        $this->assertSame(
            count($this->_expectations[$class][$method]['inputs'][$index]),
            count($args)
        );
        if ($this->_expectations[$class][$method]['regex']) {
            foreach ($this->_expectations[$class][$method]['inputs'][$index]
                     as $i => $arg) {
                $this->assertRegExp(
                    $arg,
                    $args[$i],
                    "Comparing '{$args[$i]}' against expected '$arg' " .
                    "($class::$method #$index)"
                );
            }
        } else {
            foreach ($this->_expectations[$class][$method]['inputs'][$index]
                     as $i => $arg) {
                $this->assertSame(
                    $arg,
                    $args[$i],
                    "Comparing '{$args[$i]}' against expected '$arg' " .
                    "($class::$method #$index)"
                );
            }
        }
        return $this->_expectations[$class][$method]['outputs'][$index];
    }
}
