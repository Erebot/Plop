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

require_once(
    dirname(dirname(dirname(dirname(__FILE__)))) .
    DIRECTORY_SEPARATOR . 'stubs' .
    DIRECTORY_SEPARATOR . 'Handler' .
    DIRECTORY_SEPARATOR . 'SysLog.php'
);

require_once(
    dirname(dirname(dirname(dirname(__FILE__)))) .
    DIRECTORY_SEPARATOR . 'stubs' .
    DIRECTORY_SEPARATOR . 'RecordInterface.php'
);

class   Plop_Handler_SysLog_Test
extends Plop_TestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->_handler = $this->getMock(
            'Plop_Handler_SysLog_Stub',
            array(
                '_makeSocket',
                '_encodePriority',
                '_close',
                '_mapPriority',
                '_emit',
                '_format',
                '_getStderr',
            ),
            array(),
            '',
            FALSE
        );
        $this->_record = $this->getMock('Plop_RecordInterface_Stub');
    }

    public function tearDown()
    {
        unset($this->_handler);
        parent::tearDown();
    }

    /**
     * @covers Plop_Handler_SysLog::__construct
     * @covers Plop_Handler_SysLog::__destruct
     */
    public function testConstructWithDefaultArguments()
    {
        $this->_handler
            ->expects($this->once())
            ->method('_makeSocket')
            ->will($this->returnValue($this->stderrStream));
        $this->expectStderrString('');

        $this->_handler->__construct();
        $this->assertSame(
            Plop_Handler_SysLog::DEFAULT_ADDRESS,
            $this->_handler->getAddressStub()
        );
        $this->assertSame(LOG_USER, $this->_handler->getFacilityStub());
    }

    /**
     * @covers Plop_Handler_SysLog::__construct
     * @covers Plop_Handler_SysLog::__destruct
     */
    public function testConstructWithSpecificArguments()
    {
        $this->_handler
            ->expects($this->once())
            ->method('_makeSocket')
            ->will($this->returnValue($this->stderrStream));
        $this->expectStderrString('');

        $address = 'udp://127.0.0.1:514';
        $this->_handler->__construct($address, LOG_DAEMON);
        $this->assertSame($address,     $this->_handler->getAddressStub());
        $this->assertSame(LOG_DAEMON,   $this->_handler->getFacilityStub());
    }

    /**
     * @covers                      Plop_Handler_SysLog::__construct
     * @expectedException           Plop_Exception
     * @expectedExceptionMessage    Unable to connect to the syslog
     */
    public function testConnectionError()
    {
        $this->_handler
            ->expects($this->once())
            ->method('_makeSocket')
            ->will($this->returnValue(FALSE));
        $this->_handler->__construct();
    }

    /**
     * @covers Plop_Handler_SysLog::_encodePriority
     */
    public function testPriorityEncoding()
    {
        $value1 = $this->_handler->encodePriorityStub(LOG_USER, LOG_CRIT);
        $value2 = $this->_handler->encodePriorityStub('user', 'crit');
        $this->assertSame($value1, $value2);
    }

    /**
     * @covers Plop_Handler_SysLog::_close
     */
    public function testCloseMethod()
    {
        $this->_handler
            ->expects($this->once())
            ->method('_makeSocket')
            ->will($this->returnValue($this->stderrStream));
        $this->_handler->__construct();
        $this->assertSame(
            $this->stderrStream,
            $this->_handler->getSocketStub()
        );

        $this->_handler->closeStub();
        $this->assertFalse($this->_handler->getSocketStub());
    }

    /**
     * @covers Plop_Handler_SysLog::_mapPriority
     */
    public function testPriorityMapping()
    {
        $value = $this->_handler->mapPriorityStub('CRITICAL');
        $this->assertSame('critical', $value);
    }

    /**
     * @covers Plop_Handler_SysLog::_mapPriority
     */
    public function testPriorityMapping2()
    {
        $value = $this->_handler->mapPriorityStub('FOOBAR');
        $this->assertSame('warning', $value);
    }

    /**
     * @covers Plop_Handler_SysLog::_emit
     */
    public function testEmitMethod()
    {
        $this->_handler
            ->expects($this->once())
            ->method('_makeSocket')
            ->will($this->returnValue($this->stderrStream));
        $this->_handler
            ->expects($this->once())
            ->method('_format')
            ->with($this->_record)
            ->will($this->returnValue('foo'));
        $this->_handler
            ->expects($this->once())
            ->method('_encodePriority')
            ->will($this->returnValue(42));
        $this->expectStderrString("<42>foo\0");

        $this->_handler->__construct();
        $this->_handler->emitStub($this->_record);
    }

    /**
     * @covers Plop_Handler_SysLog::_emit
     */
    public function testEmitMethod2()
    {
        $this->_handler
            ->expects($this->once())
            ->method('_getStderr')
            ->will($this->returnValue($this->stderrStream));
        $this->_handler
            ->expects($this->once())
            ->method('_format')
            ->with($this->_record)
            ->will($this->returnValue('foo'));
        $this->_handler
            ->expects($this->once())
            ->method('_encodePriority')
            ->will($this->returnValue(42));
        $this->expectStderrRegex(
            "/^exception 'Plop_Exception' with ".
            "message 'Connection lost' in .*$/m"
        );

        $this->_handler->__construct();
        $this->_handler->emitStub($this->_record);
    }
}
