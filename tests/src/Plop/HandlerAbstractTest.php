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
    dirname(dirname(dirname(__FILE__))) .
    DIRECTORY_SEPARATOR . 'stubs' .
    DIRECTORY_SEPARATOR . 'HandlerAbstract.php'
);

require_once(
    dirname(dirname(dirname(__FILE__))) .
    DIRECTORY_SEPARATOR . 'stubs' .
    DIRECTORY_SEPARATOR . 'RecordInterface.php'
);

class   Plop_HandlerAbstract_Test
extends Plop_TestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->_record      = $this->getMock('Plop_RecordInterface_Stub');
        $this->_formatter   = $this->getMock('Plop_FormatterInterface');
    }

    /**
     * @covers Plop_HandlerAbstract::__construct
     */
    public function testDefaultArguments()
    {
        $handler = $this->getMock(
            'Plop_HandlerAbstract_Stub',
            array('_emit'),
            array()
        );
        $formatter = $handler->getFormatter();
        $this->assertSame('Plop_Formatter', get_class($formatter));
    }

    /**
     * @covers Plop_HandlerAbstract::__construct
     */
    public function testDefaultArgumentsOverride()
    {
        $handler = $this->getMock(
            'Plop_HandlerAbstract_Stub',
            array('_emit'),
            array($this->_formatter)
        );
        $this->assertSame($this->_formatter, $handler->getFormatter());
    }

    /**
     * @covers Plop_HandlerAbstract::handle
     */
    public function testHandleMethod()
    {
        $handler = $this->getMock(
            'Plop_HandlerAbstract_Stub',
            array('_format', '_emit')
        );
        $handler
            ->expects($this->once())
            ->method('_format')
            ->with($this->_record)
            ->will($this->returnValue('Foo'));
        $handler
            ->expects($this->once())
            ->method('_emit')
            ->with($this->_record);
        $this->assertSame($handler, $handler->handle($this->_record));
    }
}

