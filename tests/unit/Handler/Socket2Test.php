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

class Socket2 extends \Plop_TestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->closed = false;

        $this->record  = $this->getMockBuilder('\\Plop\\Stub\\RecordInterface')->getMock();
        $this->handler = $this->getMockBuilder('\\Plop\\Stub\\Handler\\Socket')
            ->setMethods(array('getStderr', 'makeSocket'))
            ->setConstructorArgs(array())
            ->disableOriginalConstructor()
            ->getMock();

        $this->streamMock = $this->getMockBuilder('Plop_Testenv_Socket')->getMock();
        $context = stream_context_create(
            array(
                'mock' => array('object' => $this->streamMock)
            )
        );
        stream_wrapper_register('mock', 'Plop_Testenv_Socket');
        $this->socket = fopen('mock://', 'a+t', false, $context);
        stream_wrapper_unregister('mock');

        $this->streamMock
            ->expects($this->any())
            ->method('stream_close')
            ->will($this->returnCallback(array($this, 'closeStream')));

        $this->handler
            ->expects($this->once())
            ->method('makeSocket')
            ->will($this->returnValue($this->socket));
        $this->handler
            ->expects($this->once())
            ->method('getStderr')
            ->will($this->returnValue($this->stderrStream));
    }

    public function tearDown()
    {
        // This is necessary to avoid a segfault under PHP 5.2.x.
        if (is_resource($this->socket)) {
            fclose($this->socket);
        }
        unset($this->handler);
        parent::tearDown();
    }

    public function closeStream()
    {
        $this->closed = true;
        return true;
    }

    /**
     * @covers \Plop\Handler\Socket::handleError
     * @covers \Plop\Handler\Socket::close
     */
    public function testErrorHandling()
    {
        $exc = new \Plop\Exception('');
        $this->handler->createSocketStub();
        $this->handler->handleError($this->record, $exc);
        $this->assertFalse($this->closed);
    }

    /**
     * @covers \Plop\Handler\Socket::handleError
     * @covers \Plop\Handler\Socket::close
     */
    public function testErrorHandling2()
    {
        $exc = new \Plop\Exception('');
        $this->handler->setCloseOnError(true);
        $this->handler->createSocketStub();
        $this->handler->handleError($this->record, $exc);
        $this->assertTrue($this->closed);
    }
}
