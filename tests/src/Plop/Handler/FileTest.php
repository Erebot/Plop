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
    DIRECTORY_SEPARATOR . 'File.php'
);

class   Plop_Handler_File_Test
extends Plop_TestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->_handler = $this->getMock(
            'Plop_Handler_File_Stub',
            array('_open', '_close', '_flush', '_format'),
            array(),
            '',
            FALSE
        );
        $this->_record  = $this->getMock('Plop_RecordInterface');
    }

    public function tearDown()
    {
        unset($this->_handler);
        parent::tearDown();
    }

    /**
     * @covers Plop_Handler_File::__construct
     * @covers Plop_Handler_File::_close
     */
    public function testImmediateOpeningAndClosing()
    {
        $this->_handler
            ->expects($this->once())
            ->method('_open')
            ->will($this->returnValue($this->stderrStream));
        $this->_handler->__construct('php://stderr');
        $this->assertSame(
            $this->stderrStream,
            $this->_handler->getStreamStub()
        );

        $this->_handler->closeStub();
        $this->assertFalse($this->_handler->getStreamStub());
    }

    /**
     * @covers Plop_Handler_File::__construct
     * @covers Plop_Handler_File::__destruct
     */
    public function testDelayedOpening()
    {
        $this->_handler
            ->expects($this->never())
            ->method('_open');
        $this->_handler->__construct('php://stderr', 'at', TRUE);
    }

    /**
     * @covers Plop_Handler_File::_open
     */
    public function testFileOpening()
    {
        $this->_handler->__construct('php://stderr', 'a+b', TRUE);
        $this->assertFalse($this->_handler->getStreamStub());
        $stream = $this->_handler->openStub();
        $this->assertNotSame(FALSE, $stream);

        $metadata = stream_get_meta_data($stream);
        $this->assertSame('a+b', $metadata['mode']);
    }

    /**
     * @covers Plop_Handler_File::_emit
     */
    public function testEmitMethod()
    {
        $this->_handler
            ->expects($this->once())
            ->method('_open')
            ->will($this->returnValue($this->stderrStream));
        $this->_handler
            ->expects($this->once())
            ->method('_format')
            ->will($this->returnValue('abc'));
        $this->expectStderrString("abc\n");
        $this->_handler->__construct('php://stderr', 'at', TRUE);
        $this->assertFalse($this->_handler->getStreamStub());

        $this->_handler->emitStub($this->_record);
        $this->assertSame(
            $this->stderrStream,
            $this->_handler->getStreamStub()
        );
    }
}
