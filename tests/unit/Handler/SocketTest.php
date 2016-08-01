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

class Socket extends \Plop_TestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->line    = __LINE__;
        $this->handler = $this->getMockBuilder('\\Plop\\Stub\\Handler\\Socket')
            ->setMethods(
                array(
                    'makeSocket',
                    'getCurrentTime',
                    'handleError',
                    'emit',
                    'send',
                    'makePickle',
                    'createSocket',
                    'write',
                )
            )
            ->setConstructorArgs(array('::1', 1234))
            ->getMock();
        $this->record  = $this->getMockBuilder('\\Plop\\Stub\\RecordInterface')->getMock();
    }

    public function tearDown()
    {
        unset($this->handler);
        parent::tearDown();
    }

    /**
     * @covers \Plop\Handler\Socket::__construct
     * @covers \Plop\Handler\Socket::__destruct
     */
    public function testConstructorWithDefaultArguments()
    {
        $this->assertFalse($this->handler->getCloseOnError());
        $this->assertSame(1.0, $this->handler->getInitialRetryDelay());
        $this->assertSame(30.0, $this->handler->getMaximumRetryDelay());
        $this->assertSame(2.0, $this->handler->getRetryFactor());
    }

    /**
     * @covers \Plop\Handler\Socket::getCloseOnError
     * @covers \Plop\Handler\Socket::setCloseOnError
     */
    public function testCloseOnErrorAccessors()
    {
        $this->assertFalse($this->handler->getCloseOnError());
        $this->assertSame(
            $this->handler,
            $this->handler->setCloseOnError(true)
        );
        $this->assertTrue($this->handler->getCloseOnError());
        $this->assertSame(
            $this->handler,
            $this->handler->setCloseOnError(false)
        );
        $this->assertFalse($this->handler->getCloseOnError());
    }

    /**
     * @covers \Plop\Handler\Socket::setCloseOnError
     * @expectedException           \Plop\Exception
     * @expectedExceptionMessage    Invalid value
     */
    public function testCloseOnErrorAccessors2()
    {
        $this->handler->setCloseOnError('foo');
    }

    /**
     * @covers \Plop\Handler\Socket::getInitialRetryDelay
     * @covers \Plop\Handler\Socket::setInitialRetryDelay
     */
    public function testInitialRetryDelayAccessors()
    {
        $this->assertSame(1.0, $this->handler->getInitialRetryDelay());
        $this->assertSame(
            $this->handler,
            $this->handler->setInitialRetryDelay(4.2)
        );
        $this->assertSame(4.2, $this->handler->getInitialRetryDelay());
        $this->assertSame(
            $this->handler,
            $this->handler->setInitialRetryDelay(0)
        );
        $this->assertSame(0, $this->handler->getInitialRetryDelay());
    }

    /**
     * @covers \Plop\Handler\Socket::setInitialRetryDelay
     * @expectedException           \Plop\Exception
     * @expectedExceptionMessage    Invalid value
     */
    public function testInitialRetryDelayAccessors2()
    {
        $this->handler->setInitialRetryDelay('foo');
    }

    /**
     * @covers \Plop\Handler\Socket::setInitialRetryDelay
     * @expectedException           \Plop\Exception
     * @expectedExceptionMessage    Invalid value
     */
    public function testInitialRetryDelayAccessors3()
    {
        $this->handler->setInitialRetryDelay(-0.001);
    }


    /**
     * @covers \Plop\Handler\Socket::getRetryFactor
     * @covers \Plop\Handler\Socket::setRetryFactor
     */
    public function testRetryFactorAccessors()
    {
        $this->assertSame(2.0, $this->handler->getRetryFactor());
        $this->assertSame(
            $this->handler,
            $this->handler->setRetryFactor(4.2)
        );
        $this->assertSame(4.2, $this->handler->getRetryFactor());
        $this->assertSame(
            $this->handler,
            $this->handler->setRetryFactor(1)
        );
        $this->assertSame(1, $this->handler->getRetryFactor());
    }

    /**
     * @covers \Plop\Handler\Socket::setRetryFactor
     * @expectedException           \Plop\Exception
     * @expectedExceptionMessage    Invalid value
     */
    public function testRetryFactorAccessors2()
    {
        $this->handler->setRetryFactor('foo');
    }

    /**
     * @covers \Plop\Handler\Socket::setRetryFactor
     * @expectedException           \Plop\Exception
     * @expectedExceptionMessage    Invalid value
     */
    public function testRetryFactorAccessors3()
    {
        $this->handler->setRetryFactor(0.999);
    }


    /**
     * @covers \Plop\Handler\Socket::getMaximumRetryDelay
     * @covers \Plop\Handler\Socket::setMaximumRetryDelay
     */
    public function testMaximumRetryDelayAccessors()
    {
        $this->assertSame(30.0, $this->handler->getMaximumRetryDelay());
        $this->assertSame(
            $this->handler,
            $this->handler->setMaximumRetryDelay(4.2)
        );
        $this->assertSame(4.2, $this->handler->getMaximumRetryDelay());
        $this->assertSame(
            $this->handler,
            $this->handler->setMaximumRetryDelay(0)
        );
        $this->assertSame(0, $this->handler->getMaximumRetryDelay());
    }

    /**
     * @covers \Plop\Handler\Socket::setMaximumRetryDelay
     * @expectedException           \Plop\Exception
     * @expectedExceptionMessage    Invalid value
     */
    public function testMaximumRetryDelayAccessors2()
    {
        $this->handler->setMaximumRetryDelay('foo');
    }

    /**
     * @covers \Plop\Handler\Socket::setMaximumRetryDelay
     * @expectedException           \Plop\Exception
     * @expectedExceptionMessage    Invalid value
     */
    public function testMaximumRetryDelayAccessors3()
    {
        $this->handler->setMaximumRetryDelay(-0.001);
    }

    /**
     * @covers \Plop\Handler\Socket::emit
     */
    public function testEmitMethod()
    {
        $this->handler
            ->expects($this->once())
            ->method('makePickle')
            ->with($this->record)
            ->will($this->returnValue('foo'));
        $this->handler
            ->expects($this->once())
            ->method('send')
            ->with('foo');
        $this->handler
            ->expects($this->never())
            ->method('handleError');
        $this->handler->emitStub($this->record);
    }

    /**
     * @covers \Plop\Handler\Socket::emit
     */
    public function testEmitMethod2()
    {
        $exc = new \Plop\Exception('');
        $this->handler
            ->expects($this->once())
            ->method('makePickle')
            ->with($this->record)
            ->will($this->throwException($exc));
        $this->handler
            ->expects($this->never())
            ->method('send');
        $this->handler
            ->expects($this->once())
            ->method('handleError')
            ->with($this->record, $exc);
        $this->handler->emitStub($this->record);
    }

    /**
     * @covers \Plop\Handler\Socket::emit
     */
    public function testEmitMethod3()
    {
        $exc = new \Plop\Exception('');
        $this->handler
            ->expects($this->once())
            ->method('makePickle')
            ->with($this->record)
            ->will($this->returnValue('foo'));
        $this->handler
            ->expects($this->once())
            ->method('send')
            ->with('foo')
            ->will($this->throwException($exc));
        $this->handler
            ->expects($this->once())
            ->method('handleError')
            ->with($this->record, $exc);
        $this->handler->emitStub($this->record);
    }

    /**
     * @covers \Plop\Handler\Socket::createSocket
     */
    public function testCreateSocketMethod()
    {
        $this->handler
            ->expects($this->once())
            ->method('getCurrentTime')
            ->will($this->returnCallback('time'));
        $res = tmpfile();
        $this->handler
            ->expects($this->once())
            ->method('makeSocket')
            ->will($this->returnValue($res));
        $this->handler->createSocketStub();
    }

    /**
     * @covers \Plop\Handler\Socket::createSocket
     */
    public function testCreateSocketMethod2()
    {
        $time = time();
        $this->handler->setInitialRetryDelay(2);
        $this->handler->setRetryFactor(1);
        $this->handler->setMaximumRetryDelay(1);
        $this->handler
            ->expects($this->exactly(3))
            ->method('getCurrentTime')
            ->will($this->onConsecutiveCalls($time, $time + 1, $time + 5));
        $this->handler
            ->expects($this->exactly(2))
            ->method('makeSocket')
            ->will($this->returnValue(false));

        // Call it three times in a row:
        // -    the first one initializes the retry delay
        // -    the second one tests a call when the retry delay
        //      has not been exceeded yet
        // -    the third one tests the bounds of the delay
        $this->handler->createSocketStub();
        $this->assertSame($time + 2, $this->handler->getRetryTimeStub());
        $this->handler->createSocketStub();
        $this->assertSame($time + 2, $this->handler->getRetryTimeStub());
        $this->handler->createSocketStub();
        $this->assertSame($time + 5 + 1, $this->handler->getRetryTimeStub());
    }

    /**
     * @covers \Plop\Handler\Socket::makePickle
     */
    public function testMakePickleMethod()
    {
        $record  = $this->getMockBuilder('\\Plop\\Stub\\RecordInterface')
            ->setConstructorArgs(array())
            ->setMockClassName('Plop_Record_Mock')
            ->getMock();
        $record
            ->expects($this->once())
            ->method('serialize')
            ->will($this->returnValue(serialize(array('foo' => 'bar'))));
        $this->assertSame(
            "\0\0\0007" .
            'C:16:"Plop_Record_Mock":26:{a:1:{s:3:"foo";s:3:"bar";}}',
            $this->handler->makePickleStub($record)
        );
    }

    /**
     * @covers \Plop\Handler\Socket::send
     */
    public function testSendMethod()
    {
        $this->handler
            ->expects($this->once())
            ->method('createSocket');

        // If no connection could be established,
        // the method returns false immediately.
        $this->assertFalse($this->handler->sendStub('foo'));
    }

    /**
     * @covers \Plop\Handler\Socket::send
     */
    public function testSendMethod2()
    {
        $res = tmpfile();
        $this->handler
            ->expects($this->once())
            ->method('createSocket')
            ->will($this->returnCallback(
                array($this->handler, 'createSocketStub')
            ));
        $this->handler
            ->expects($this->once())
            ->method('write')
            ->will($this->returnCallback(
                array($this->handler, 'writeStub')
            ));
        $this->handler
            ->expects($this->once())
            ->method('makeSocket')
            ->will($this->returnValue($res));

        // The method returns true when all of the data
        // has been sent successfully.
        $this->assertTrue($this->handler->sendStub('foo'));
        $this->assertNotEquals(-1, fseek($res, 0, SEEK_SET));
        $this->assertSame('foo', fread($res, 8192));
        $this->assertTrue(feof($res));
    }

    /**
     * @covers \Plop\Handler\Socket::send
     * @expectedException           \Plop\Exception
     * @expectedExceptionMessage    Connection lost
     */
    public function testSendMethod3()
    {
        $res = tmpfile();
        $this->handler
            ->expects($this->once())
            ->method('createSocket')
            ->will($this->returnCallback(
                array($this->handler, 'createSocketStub')
            ));
        $this->handler
            ->expects($this->once())
            ->method('makeSocket')
            ->will($this->returnValue($res));
        $this->handler
            ->expects($this->once())
            ->method('write')
            ->will($this->returnValue(false));

        // The method throws an exception when the connection is lost.
        $this->handler->sendStub('bar');
    }
}
