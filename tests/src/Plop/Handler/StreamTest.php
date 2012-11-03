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
    DIRECTORY_SEPARATOR . 'Stream.php'
);

require_once(
    dirname(dirname(dirname(dirname(__FILE__)))) .
    DIRECTORY_SEPARATOR . 'stubs' .
    DIRECTORY_SEPARATOR . 'RecordInterface.php'
);

class   Plop_Handler_Stream_Test
extends Plop_TestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->_handler = $this->getMock(
            'Plop_Handler_Stream_Stub',
            array('_format'),
            array($this->stderrStream, 'ISO-8859-1')
        );
        $this->_record  = $this->getMock('Plop_RecordInterface_Stub');
    }

    public function tearDown()
    {
        unset($this->_handler);
        parent::tearDown();
    }

    /**
     * @covers Plop_Handler_Stream::__construct
     * @covers Plop_Handler_Stream::_emit
     * @covers Plop_Handler_Stream::_flush
     */
    public function testLogging()
    {
        $this->_handler
            ->expects($this->once())
            ->method('_format')
            ->with($this->_record)
            ->will($this->returnValue('abc'));
        $this->_handler->emitStub($this->_record);
        $this->expectStderrString("abc\n");
    }
}
