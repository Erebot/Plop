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

require_once(
    dirname(dirname(dirname(dirname(__FILE__)))) .
    DIRECTORY_SEPARATOR . 'stubs' .
    DIRECTORY_SEPARATOR . 'RecordInterface.php'
);

class   Plop_Handler_Socket2_Test
extends Plop_TestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->_closed = FALSE;

        $this->_record  = $this->getMock('Plop_RecordInterface_Stub');
        $this->_handler = $this->getMock(
            'Plop_Handler_Socket_Stub',
            array('_getStderr', '_makeSocket'),
            array(),
            '',
            FALSE
        );

        $this->_streamMock = $this->getMock('Plop_Testenv_Socket');
        $context = stream_context_create(
            array(
                'mock' => array('object' => $this->_streamMock)
            )
        );
        stream_wrapper_register('mock', 'Plop_Testenv_Socket');
        $this->_socket = fopen('mock://', 'a+t', FALSE, $context);
        stream_wrapper_unregister('mock');

        $this->_streamMock
            ->expects($this->any())
            ->method('stream_close')
            ->will($this->returnCallback(array($this, 'stream_close')));

        $this->_handler
            ->expects($this->once())
            ->method('_makeSocket')
            ->will($this->returnValue($this->_socket));
        $this->_handler
            ->expects($this->once())
            ->method('_getStderr')
            ->will($this->returnValue($this->stderrStream));
    }

    public function tearDown()
    {
        // This is necessary to avoid a segfault under PHP 5.2.x.
        if (is_resource($this->_socket)) {
            fclose($this->_socket);
        }
        unset($this->_handler);
        parent::tearDown();
    }

    public function stream_close()
    {
        $this->_closed = TRUE;
        return TRUE;
    }

    /**
     * @covers Plop_Handler_Socket::handleError
     * @covers Plop_Handler_Socket::_close
     */
    public function testErrorHandling()
    {
        $exc = new Plop_Exception('');
        $this->_handler->createSocketStub();
        $this->_handler->handleError($this->_record, $exc);
        $this->assertFalse($this->_closed);
    }

    /**
     * @covers Plop_Handler_Socket::handleError
     * @covers Plop_Handler_Socket::_close
     */
    public function testErrorHandling2()
    {
        $exc = new Plop_Exception('');
        $this->_handler->setCloseOnError(TRUE);
        $this->_handler->createSocketStub();
        $this->_handler->handleError($this->_record, $exc);
        $this->assertTrue($this->_closed);
    }
}
