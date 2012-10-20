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

class   Plop_LoggerAbstract_Test
extends Plop_TestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->_logger = $this->getMock(
            'Plop_LoggerAbstract',
            array(
                'log',
                'getLevel',
                'setLevel',
                'isEnabledFor',
                'getFile',
                'getClass',
                'getMethod',
                'getRecordFactory',
                'setRecordFactory',
                'addHandler',
                'removeHandler',
                'getHandlers',
            ),
            array(),
            '',
            FALSE
        );
    }

    public function providerLevels()
    {
        return array(
            array('debug',      Plop::DEBUG),
            array('info',       Plop::INFO),
            array('warning',    Plop::WARNING),
            array('warn',       Plop::WARN),
            array('error',      Plop::ERROR),
            array('critical',   Plop::CRITICAL),
            array('fatal',      Plop::CRITICAL),
        );
    }

    /**
     * @covers Plop_LoggerAbstract::debug
     * @covers Plop_LoggerAbstract::info
     * @covers Plop_LoggerAbstract::warning
     * @covers Plop_LoggerAbstract::warn
     * @covers Plop_LoggerAbstract::error
     * @covers Plop_LoggerAbstract::critical
     * @covers Plop_LoggerAbstract::fatal
     * @dataProvider providerLevels
     */
    public function testMessageOnly($method, $level)
    {
        $input  = 'Foo';
        $output = 'Bar';
        $this->_logger
            ->expects($this->once())
            ->method('log')
            ->with($level, $input, array(), NULL)
            ->will($this->returnValue($output));
        $this->assertSame($output, $this->_logger->$method($input));
    }

    /**
     * @covers Plop_LoggerAbstract::debug
     * @covers Plop_LoggerAbstract::info
     * @covers Plop_LoggerAbstract::warning
     * @covers Plop_LoggerAbstract::warn
     * @covers Plop_LoggerAbstract::error
     * @covers Plop_LoggerAbstract::critical
     * @covers Plop_LoggerAbstract::fatal
     * @dataProvider providerLevels
     */
    public function testOtherArguments($method, $level)
    {
        $input  = 'Foo';
        $output = 'Bar';
        $args   = array('foo' => 'bar');
        $exc    = new Plop_Exception('');
        $this->_logger
            ->expects($this->once())
            ->method('log')
            ->with($level, $input, $args, $exc)
            ->will($this->returnValue($output));
        $this->assertSame(
            $output,
            $this->_logger->$method($input, $args, $exc)
        );
    }

    /**
     * @covers Plop_LoggerAbstract::exception
     */
    public function testExceptionWithMessageOnly()
    {
        $input  = 'Foo';
        $output = 'Bar';
        $exc    = new Plop_Exception('');
        $this->_logger
            ->expects($this->once())
            ->method('log')
            ->with(Plop::ERROR, $input, array(), $exc)
            ->will($this->returnValue($output));
        $this->assertSame($output, $this->_logger->exception($input, $exc));
    }

    /**
     * @covers Plop_LoggerAbstract::exception
     */
    public function testExceptionWithOtherArguments()
    {
        $input  = 'Foo';
        $output = 'Bar';
        $args   = array('foo' => 'bar');
        $exc    = new Plop_Exception('');
        $this->_logger
            ->expects($this->once())
            ->method('log')
            ->with(Plop::ERROR, $input, $args, $exc)
            ->will($this->returnValue($output));
        $this->assertSame(
            $output,
            $this->_logger->exception($input, $exc, $args)
        );
    }
}
