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

class   Plop_HandlerAbstract2_Test
extends Plop_TestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->_record      = $this->getMock('Plop_RecordInterface_Stub');
        $this->_formatter   = $this->getMock('Plop_FormatterInterface');
        $this->_handler = $this->getMock(
            'Plop_HandlerAbstract_Stub',
            array('_emit')
        );
    }

    /**
     * @covers Plop_HandlerAbstract::getFormatter
     * @covers Plop_HandlerAbstract::setFormatter
     */
    public function testFormatterAccessors()
    {
        $this->assertNotSame(
            $this->_formatter,
            $this->_handler->getFormatter()
        );
        $this->assertSame(
            $this->_handler,
            $this->_handler->setFormatter($this->_formatter)
        );
        $this->assertSame($this->_formatter, $this->_handler->getFormatter());
    }

    /**
     * @covers Plop_HandlerAbstract::_format
     */
    public function testFormatMethod()
    {
        $value = 'Foo';
        $this->_formatter
            ->expects($this->once())
            ->method('format')
            ->with($this->_record)
            ->will($this->returnValue($value));
        $this->_handler->setFormatter($this->_formatter);
        $this->assertSame($value, $this->_handler->formatStub($this->_record));
    }

    /**
     * @covers Plop_HandlerAbstract::handleError
     */
    public function testErrorHandling()
    {
        $line       = __LINE__ + 1;
        $exc        = new Plop_Exception('test');
        $handler    = $this->getMock(
            'Plop_HandlerAbstract_Stub',
            array('_getStderr', '_emit')
        );
        $handler
            ->expects($this->once())
            ->method('_getStderr')
            ->will($this->returnValue($this->stderrStream));

        $this->expectStderrRegex(
            "#exception 'Plop_Exception' with message" .
            " 'test' in [^\\r\\n]+:$line(\\r\\n?|\\n).*#m"
        );
        $this->assertSame(
            $handler,
            $handler->handleError($this->_record, $exc)
        );
    }
}
