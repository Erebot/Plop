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
    DIRECTORY_SEPARATOR . 'Socket.php'
);

class   Plop_Handler_Socket_Test
extends Plop_TestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->_line    = __LINE__;
        $this->_handler = $this->getMock(
            'Plop_Handler_Socket_Stub',
            array(
                '_makeSocket',
                '_getCurrentTime',
                'handleError',
                '_emit',
                '_send',
                '_makePickle',
                '_createSocket',
                '_write',
            ),
            array('::1', 1234)
        );
        $this->_record  = $this->getMock('Plop_RecordInterface');
    }

    public function tearDown()
    {
        unset($this->_handler);
        parent::tearDown();
    }

    /**
     * @covers Plop_Handler_Socket::__construct
     * @covers Plop_Handler_Socket::__destruct
     */
    public function testConstructorWithDefaultArguments()
    {
        $this->assertFalse($this->_handler->getCloseOnError());
        $this->assertSame(1.0, $this->_handler->getInitialRetryDelay());
        $this->assertSame(30.0, $this->_handler->getMaximumRetryDelay());
        $this->assertSame(2.0, $this->_handler->getRetryFactor());
    }

    /**
     * @covers Plop_Handler_Socket::getCloseOnError
     * @covers Plop_Handler_Socket::setCloseOnError
     */
    public function testCloseOnErrorAccessors()
    {
        $this->assertFalse($this->_handler->getCloseOnError());
        $this->assertSame(
            $this->_handler,
            $this->_handler->setCloseOnError(TRUE)
        );
        $this->assertTrue($this->_handler->getCloseOnError());
        $this->assertSame(
            $this->_handler,
            $this->_handler->setCloseOnError(FALSE)
        );
        $this->assertFalse($this->_handler->getCloseOnError());
    }

    /**
     * @covers Plop_Handler_Socket::setCloseOnError
     * @expectedException           Plop_Exception
     * @expectedExceptionMessage    Invalid value
     */
    public function testCloseOnErrorAccessors2()
    {
        $this->_handler->setCloseOnError('foo');
    }

    /**
     * @covers Plop_Handler_Socket::getInitialRetryDelay
     * @covers Plop_Handler_Socket::setInitialRetryDelay
     */
    public function testInitialRetryDelayAccessors()
    {
        $this->assertSame(1.0, $this->_handler->getInitialRetryDelay());
        $this->assertSame(
            $this->_handler,
            $this->_handler->setInitialRetryDelay(4.2)
        );
        $this->assertSame(4.2, $this->_handler->getInitialRetryDelay());
        $this->assertSame(
            $this->_handler,
            $this->_handler->setInitialRetryDelay(0)
        );
        $this->assertSame(0, $this->_handler->getInitialRetryDelay());
    }

    /**
     * @covers Plop_Handler_Socket::setInitialRetryDelay
     * @expectedException           Plop_Exception
     * @expectedExceptionMessage    Invalid value
     */
    public function testInitialRetryDelayAccessors2()
    {
        $this->_handler->setInitialRetryDelay('foo');
    }

    /**
     * @covers Plop_Handler_Socket::setInitialRetryDelay
     * @expectedException           Plop_Exception
     * @expectedExceptionMessage    Invalid value
     */
    public function testInitialRetryDelayAccessors3()
    {
        $this->_handler->setInitialRetryDelay(-0.001);
    }


    /**
     * @covers Plop_Handler_Socket::getRetryFactor
     * @covers Plop_Handler_Socket::setRetryFactor
     */
    public function testRetryFactorAccessors()
    {
        $this->assertSame(2.0, $this->_handler->getRetryFactor());
        $this->assertSame(
            $this->_handler,
            $this->_handler->setRetryFactor(4.2)
        );
        $this->assertSame(4.2, $this->_handler->getRetryFactor());
        $this->assertSame(
            $this->_handler,
            $this->_handler->setRetryFactor(1)
        );
        $this->assertSame(1, $this->_handler->getRetryFactor());
    }

    /**
     * @covers Plop_Handler_Socket::setRetryFactor
     * @expectedException           Plop_Exception
     * @expectedExceptionMessage    Invalid value
     */
    public function testRetryFactorAccessors2()
    {
        $this->_handler->setRetryFactor('foo');
    }

    /**
     * @covers Plop_Handler_Socket::setRetryFactor
     * @expectedException           Plop_Exception
     * @expectedExceptionMessage    Invalid value
     */
    public function testRetryFactorAccessors3()
    {
        $this->_handler->setRetryFactor(0.999);
    }


    /**
     * @covers Plop_Handler_Socket::getMaximumRetryDelay
     * @covers Plop_Handler_Socket::setMaximumRetryDelay
     */
    public function testMaximumRetryDelayAccessors()
    {
        $this->assertSame(30.0, $this->_handler->getMaximumRetryDelay());
        $this->assertSame(
            $this->_handler,
            $this->_handler->setMaximumRetryDelay(4.2)
        );
        $this->assertSame(4.2, $this->_handler->getMaximumRetryDelay());
        $this->assertSame(
            $this->_handler,
            $this->_handler->setMaximumRetryDelay(0)
        );
        $this->assertSame(0, $this->_handler->getMaximumRetryDelay());
    }

    /**
     * @covers Plop_Handler_Socket::setMaximumRetryDelay
     * @expectedException           Plop_Exception
     * @expectedExceptionMessage    Invalid value
     */
    public function testMaximumRetryDelayAccessors2()
    {
        $this->_handler->setMaximumRetryDelay('foo');
    }

    /**
     * @covers Plop_Handler_Socket::setMaximumRetryDelay
     * @expectedException           Plop_Exception
     * @expectedExceptionMessage    Invalid value
     */
    public function testMaximumRetryDelayAccessors3()
    {
        $this->_handler->setMaximumRetryDelay(-0.001);
    }

    /**
     * @covers Plop_Handler_Socket::_emit
     */
    public function testEmitMethod()
    {
        $this->_handler
            ->expects($this->once())
            ->method('_makePickle')
            ->with($this->_record)
            ->will($this->returnValue('foo'));
        $this->_handler
            ->expects($this->once())
            ->method('_send')
            ->with('foo');
        $this->_handler
            ->expects($this->never())
            ->method('handleError');
        $this->_handler->emitStub($this->_record);
    }

    /**
     * @covers Plop_Handler_Socket::_emit
     */
    public function testEmitMethod2()
    {
        $exc = new Plop_Exception('');
        $this->_handler
            ->expects($this->once())
            ->method('_makePickle')
            ->with($this->_record)
            ->will($this->throwException($exc));
        $this->_handler
            ->expects($this->never())
            ->method('_send');
        $this->_handler
            ->expects($this->once())
            ->method('handleError')
            ->with($this->_record, $exc);
        $this->_handler->emitStub($this->_record);
    }

    /**
     * @covers Plop_Handler_Socket::_emit
     */
    public function testEmitMethod3()
    {
        $exc = new Plop_Exception('');
        $this->_handler
            ->expects($this->once())
            ->method('_makePickle')
            ->with($this->_record)
            ->will($this->returnValue('foo'));
        $this->_handler
            ->expects($this->once())
            ->method('_send')
            ->with('foo')
            ->will($this->throwException($exc));
        $this->_handler
            ->expects($this->once())
            ->method('handleError')
            ->with($this->_record, $exc);
        $this->_handler->emitStub($this->_record);
    }

    /**
     * @covers Plop_Handler_Socket::_createSocket
     */
    public function testCreateSocketMethod()
    {
        $this->_handler
            ->expects($this->once())
            ->method('_getCurrentTime')
            ->will($this->returnCallback('time'));
        $res = tmpfile();
        $this->_handler
            ->expects($this->once())
            ->method('_makeSocket')
            ->will($this->returnValue($res));
        $this->_handler->createSocketStub();
    }

    /**
     * @covers Plop_Handler_Socket::_createSocket
     */
    public function testCreateSocketMethod2()
    {
        $time = time();
        $this->_handler->setInitialRetryDelay(2);
        $this->_handler->setRetryFactor(1);
        $this->_handler->setMaximumRetryDelay(1);
        $this->_handler
            ->expects($this->exactly(3))
            ->method('_getCurrentTime')
            ->will($this->onConsecutiveCalls($time, $time + 1, $time + 5));
        $this->_handler
            ->expects($this->exactly(2))
            ->method('_makeSocket')
            ->will($this->returnValue(FALSE));

        // Call it three times in a row:
        // -    the first one initializes the retry delay
        // -    the second one tests a call when the retry delay
        //      has not been exceeded yet
        // -    the third one tests the bounds of the delay
        $this->_handler->createSocketStub();
        $this->assertSame($time + 2, $this->_handler->getRetryTimeStub());
        $this->_handler->createSocketStub();
        $this->assertSame($time + 2, $this->_handler->getRetryTimeStub());
        $this->_handler->createSocketStub();
        $this->assertSame($time + 5 + 1, $this->_handler->getRetryTimeStub());
    }

    /**
     * @covers Plop_Handler_Socket::_makePickle
     */
    public function testMakePickleMethod()
    {
        $record  = $this->getMock(
            'Plop_RecordInterface',
            array(),
            array(),
            'Plop_Record_Mock'
        );
        $record
            ->expects($this->once())
            ->method('serialize')
            ->will($this->returnValue(serialize(array('foo' => 'bar'))));
        $this->assertSame(
            "\0\0\0007" .
            'C:16:"Plop_Record_Mock":26:{a:1:{s:3:"foo";s:3:"bar";}}',
            $this->_handler->makePickleStub($record)
        );
    }

    /**
     * @covers Plop_Handler_Socket::_send
     */
    public function testSendMethod()
    {
        $this->_handler
            ->expects($this->once())
            ->method('_createSocket');

        // If no connection could be established,
        // the method returns FALSE immediately.
        $this->assertFalse($this->_handler->sendStub('foo'));
    }

    /**
     * @covers Plop_Handler_Socket::_send
     */
    public function testSendMethod2()
    {
        $res = tmpfile();
        $this->_handler
            ->expects($this->once())
            ->method('_createSocket')
            ->will($this->returnCallback(
                array($this->_handler, 'createSocketStub')
            ));
        $this->_handler
            ->expects($this->once())
            ->method('_write')
            ->will($this->returnCallback(
                array($this->_handler, 'writeStub')
            ));
        $this->_handler
            ->expects($this->once())
            ->method('_makeSocket')
            ->will($this->returnValue($res));

        // The method returns TRUE when all of the data
        // has been sent successfully.
        $this->assertTrue($this->_handler->sendStub('foo'));
        $this->assertNotEquals(-1, fseek($res, 0, SEEK_SET));
        $this->assertSame('foo', fread($res, 8192));
        $this->assertTrue(feof($res));
    }

    /**
     * @covers Plop_Handler_Socket::_send
     * @expectedException           Plop_Exception
     * @expectedExceptionMessage    Connection lost
     */
    public function testSendMethod3()
    {
        $res = tmpfile();
        $this->_handler
            ->expects($this->once())
            ->method('_createSocket')
            ->will($this->returnCallback(
                array($this->_handler, 'createSocketStub')
            ));
        $this->_handler
            ->expects($this->once())
            ->method('_makeSocket')
            ->will($this->returnValue($res));
        $this->_handler
            ->expects($this->once())
            ->method('_write')
            ->will($this->returnValue(FALSE));

        // The method throws an exception when the connection is lost.
        $this->_handler->sendStub('bar');
    }

}
