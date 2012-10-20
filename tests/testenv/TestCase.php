<?php
/*
    This file is part of Plop, a simple logging library for PHP.

    Copyright © 2010-2012 François Poirotte

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

class   Plop_TestCase
extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->_expectations = array();
    }

    protected function _setExpectations($obj, $method, $inputs, $outputs)
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
        foreach ($this->_expectations[$class][$method]['inputs'][$index]
                 as $i => $arg) {
            $this->assertSame(
                $arg,
                $args[$i],
                "Comparing '{$args[$i]}' against expected '$arg' " .
                "($class::$method #$index)"
            );
        }
        return $this->_expectations[$class][$method]['outputs'][$index];
    }
}
