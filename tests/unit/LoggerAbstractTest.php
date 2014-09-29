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

namespace Plop\Tests;

class LoggerAbstract extends \Plop_TestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->logger = $this->getMock(
            '\\Plop\\LoggerAbstract',
            array(
                'log',
                'getLevel',
                'setLevel',
                'isEnabledFor',
                'getNamespace',
                'getClass',
                'getMethod',
                'getRecordFactory',
                'setRecordFactory',
                'getFilters',
                'setFilters',
                'getHandlers',
                'setHandlers',
            ),
            array(),
            '',
            false
        );
    }

    public function providerLevels()
    {
        return array(
            array('debug',      \Plop\DEBUG),
            array('info',       \Plop\INFO),
            array('warning',    \Plop\WARNING),
            array('warn',       \Plop\WARN),
            array('error',      \Plop\ERROR),
            array('critical',   \Plop\CRITICAL),
            array('fatal',      \Plop\CRITICAL),
        );
    }

    /**
     * @covers \Plop\LoggerAbstract::debug
     * @covers \Plop\LoggerAbstract::info
     * @covers \Plop\LoggerAbstract::warning
     * @covers \Plop\LoggerAbstract::warn
     * @covers \Plop\LoggerAbstract::error
     * @covers \Plop\LoggerAbstract::critical
     * @covers \Plop\LoggerAbstract::fatal
     * @dataProvider providerLevels
     */
    public function testMessageOnly($method, $level)
    {
        $input  = 'Foo';
        $output = 'Bar';
        $this->logger
            ->expects($this->once())
            ->method('log')
            ->with($level, $input, array(), null)
            ->will($this->returnValue($output));
        $this->assertSame($output, $this->logger->$method($input));
    }

    /**
     * @covers \Plop\LoggerAbstract::debug
     * @covers \Plop\LoggerAbstract::info
     * @covers \Plop\LoggerAbstract::warning
     * @covers \Plop\LoggerAbstract::warn
     * @covers \Plop\LoggerAbstract::error
     * @covers \Plop\LoggerAbstract::critical
     * @covers \Plop\LoggerAbstract::fatal
     * @dataProvider providerLevels
     */
    public function testOtherArguments($method, $level)
    {
        $input  = 'Foo';
        $output = 'Bar';
        $args   = array('foo' => 'bar');
        $exc    = new \Plop\Exception('');
        $this->logger
            ->expects($this->once())
            ->method('log')
            ->with($level, $input, $args, $exc)
            ->will($this->returnValue($output));
        $this->assertSame(
            $output,
            $this->logger->$method($input, $args, $exc)
        );
    }

    /**
     * @covers \Plop\LoggerAbstract::exception
     */
    public function testExceptionWithMessageOnly()
    {
        $input  = 'Foo';
        $output = 'Bar';
        $exc    = new \Plop\Exception('');
        $this->logger
            ->expects($this->once())
            ->method('log')
            ->with(\Plop\ERROR, $input, array(), $exc)
            ->will($this->returnValue($output));
        $this->assertSame($output, $this->logger->exception($input, $exc));
    }

    /**
     * @covers \Plop\LoggerAbstract::exception
     */
    public function testExceptionWithOtherArguments()
    {
        $input  = 'Foo';
        $output = 'Bar';
        $args   = array('foo' => 'bar');
        $exc    = new \Plop\Exception('');
        $this->logger
            ->expects($this->once())
            ->method('log')
            ->with(\Plop\ERROR, $input, $args, $exc)
            ->will($this->returnValue($output));
        $this->assertSame(
            $output,
            $this->logger->exception($input, $exc, $args)
        );
    }
}
