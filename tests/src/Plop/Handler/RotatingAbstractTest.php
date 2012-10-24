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
    DIRECTORY_SEPARATOR . 'RotatingAbstract.php'
);

class   Plop_Handler_RotatingAbstract_Test
extends Plop_TestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->_handler = $this->getMock(
            'Plop_Handler_RotatingAbstract_Stub',
            array(
                '_doRollover',
                '_shouldRollover',
                'handleError',
                '_open',
                '_format',
            ),
            array(''),
            '',
            FALSE
        );
        $this->_record  = $this->getMock('Plop_RecordInterface');

        $this->_handler
            ->expects($this->once())
            ->method('_open')
            ->will($this->returnValue($this->stderrStream));
        $this->_handler->__construct('');
    }

    public function tearDown()
    {
        unset($this->_handler);
        parent::tearDown();
    }

    /**
     * @cover Plop_Handler_RotatingAbstract::_emit
     */
    public function testEmitWithoutRollover()
    {
        $this->_handler
            ->expects($this->once())
            ->method('_format')
            ->with($this->_record)
            ->will($this->returnValue('test'));
        $this->_handler
            ->expects($this->once())
            ->method('_shouldRollover')
            ->with($this->_record)
            ->will($this->returnValue(FALSE));
        $this->_handler
            ->expects($this->never())
            ->method('_doRollover');
        $this->_handler
            ->expects($this->never())
            ->method('handleError');
        $this->_handler->emitStub($this->_record);
        $this->expectStderrString("test\n");
    }

    /**
     * @cover Plop_Handler_RotatingAbstract::_emit
     */
    public function testEmitWithRollover()
    {
        $this->_handler
            ->expects($this->once())
            ->method('_format')
            ->with($this->_record)
            ->will($this->returnValue('test'));
        $this->_handler
            ->expects($this->once())
            ->method('_shouldRollover')
            ->with($this->_record)
            ->will($this->returnValue(TRUE));
        $this->_handler
            ->expects($this->once())
            ->method('_doRollover');
        $this->_handler
            ->expects($this->never())
            ->method('handleError');
        $this->_handler->emitStub($this->_record);
        $this->expectStderrString("test\n");
    }

    /**
     * @cover Plop_Handler_RotatingAbstract::_emit
     */
    public function testEmitThrowingException()
    {
        $exc = new Plop_Exception('');
        $this->_handler
            ->expects($this->once())
            ->method('_shouldRollover')
            ->with($this->_record)
            ->will($this->throwException($exc));
        $this->_handler
            ->expects($this->never())
            ->method('_doRollover');
        $this->_handler
            ->expects($this->once())
            ->method('handleError')
            ->with($this->_record, $exc);
        $this->_handler->emitStub($this->_record);
   }
}
