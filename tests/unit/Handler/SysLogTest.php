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

namespace Plop\Tests\Handler;

class SysLog extends \Plop_TestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->handler = $this->getMock(
            '\\Plop\\Stub\\Handler\\SysLog',
            array(
                'makeSocket',
                'encodePriority',
                'close',
                'mapPriority',
                'emit',
                'format',
                'getStderr',
            ),
            array(),
            '',
            false
        );
        $this->record = $this->getMock('\\Plop\\Stub\\RecordInterface');
    }

    public function tearDown()
    {
        unset($this->handler);
        parent::tearDown();
    }

    /**
     * @covers \Plop\Handler\SysLog::__construct
     * @covers \Plop\Handler\SysLog::__destruct
     */
    public function testConstructWithDefaultArguments()
    {
        $this->handler
            ->expects($this->once())
            ->method('makeSocket')
            ->will($this->returnValue($this->stderrStream));
        $this->expectStderrString('');

        $this->handler->__construct();
        $this->assertSame(
            \Plop\Handler\SysLog::DEFAULT_ADDRESS,
            $this->handler->getAddressStub()
        );
        $this->assertSame(LOG_USER, $this->handler->getFacilityStub());
    }

    /**
     * @covers \Plop\Handler\SysLog::__construct
     * @covers \Plop\Handler\SysLog::__destruct
     */
    public function testConstructWithSpecificArguments()
    {
        $this->handler
            ->expects($this->once())
            ->method('makeSocket')
            ->will($this->returnValue($this->stderrStream));
        $this->expectStderrString('');

        $address = 'udp://127.0.0.1:514';
        $this->handler->__construct($address, LOG_DAEMON);
        $this->assertSame($address, $this->handler->getAddressStub());
        $this->assertSame(LOG_DAEMON, $this->handler->getFacilityStub());
    }

    /**
     * @covers                      \Plop\Handler\SysLog::__construct
     * @expectedException           \Plop\Exception
     * @expectedExceptionMessage    Unable to connect to the syslog
     */
    public function testConnectionError()
    {
        $this->handler
            ->expects($this->once())
            ->method('makeSocket')
            ->will($this->returnValue(false));
        $this->handler->__construct();
    }

    /**
     * @covers \Plop\Handler\SysLog::encodePriority
     */
    public function testPriorityEncoding()
    {
        $value1 = $this->handler->encodePriorityStub(LOG_USER, LOG_CRIT);
        $value2 = $this->handler->encodePriorityStub('user', 'crit');
        $this->assertSame($value1, $value2);
    }

    /**
     * @covers \Plop\Handler\SysLog::close
     */
    public function testCloseMethod()
    {
        $this->handler
            ->expects($this->once())
            ->method('makeSocket')
            ->will($this->returnValue($this->stderrStream));
        $this->handler->__construct();
        $this->assertSame(
            $this->stderrStream,
            $this->handler->getSocketStub()
        );

        $this->handler->closeStub();
        $this->assertFalse($this->handler->getSocketStub());
    }

    /**
     * @covers \Plop\Handler\SysLog::mapPriority
     */
    public function testPriorityMapping()
    {
        $value = $this->handler->mapPriorityStub('CRITICAL');
        $this->assertSame('critical', $value);
    }

    /**
     * @covers \Plop\Handler\SysLog::mapPriority
     */
    public function testPriorityMapping2()
    {
        $value = $this->handler->mapPriorityStub('FOOBAR');
        $this->assertSame('warning', $value);
    }

    /**
     * @covers \Plop\Handler\SysLog::emit
     */
    public function testEmitMethod()
    {
        $this->handler
            ->expects($this->once())
            ->method('makeSocket')
            ->will($this->returnValue($this->stderrStream));
        $this->handler
            ->expects($this->once())
            ->method('format')
            ->with($this->record)
            ->will($this->returnValue('foo'));
        $this->handler
            ->expects($this->once())
            ->method('encodePriority')
            ->will($this->returnValue(42));
        $this->expectStderrString("<42>foo\0");

        $this->handler->__construct();
        $this->handler->emitStub($this->record);
    }

    /**
     * @covers \Plop\Handler\SysLog::emit
     */
    public function testEmitMethod2()
    {
        $this->handler
            ->expects($this->once())
            ->method('getStderr')
            ->will($this->returnValue($this->stderrStream));
        $this->handler
            ->expects($this->once())
            ->method('format')
            ->with($this->record)
            ->will($this->returnValue('foo'));
        $this->handler
            ->expects($this->once())
            ->method('encodePriority')
            ->will($this->returnValue(42));
        $this->expectStderrRegex(
            "/^exception 'Plop\\\\Exception' with ".
            "message 'Connection lost' in .*$/m"
        );

        $this->handler->__construct();
        $this->handler->emitStub($this->record);
    }
}
