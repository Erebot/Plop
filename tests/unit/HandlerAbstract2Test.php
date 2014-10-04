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

namespace Plop\Tests\HandlerAbstract;

class Test2 extends \Plop_TestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->record       = $this->getMock('\\Plop\\Stub\\RecordInterface');
        $this->formatter    = $this->getMock('\\Plop\\FormatterInterface');
        $this->handler      = $this->getMock(
            '\\Plop\\Stub\\HandlerAbstract',
            array('emit')
        );
    }

    /**
     * @covers \Plop\HandlerAbstract::getFormatter
     * @covers \Plop\HandlerAbstract::setFormatter
     */
    public function testFormatterAccessors()
    {
        $this->assertNotSame(
            $this->formatter,
            $this->handler->getFormatter()
        );
        $this->assertSame(
            $this->handler,
            $this->handler->setFormatter($this->formatter)
        );
        $this->assertSame($this->formatter, $this->handler->getFormatter());
    }

    /**
     * @covers \Plop\HandlerAbstract::format
     */
    public function testFormatMethod()
    {
        $value = 'Foo';
        $this->formatter
            ->expects($this->once())
            ->method('format')
            ->with($this->record)
            ->will($this->returnValue($value));
        $this->handler->setFormatter($this->formatter);
        $this->assertSame($value, $this->handler->formatStub($this->record));
    }

    /**
     * @covers \Plop\HandlerAbstract::handleError
     */
    public function testErrorHandling()
    {
        $line       = __LINE__ + 1;
        $exc        = new \Plop\Exception('test');
        $handler    = $this->getMock(
            '\\Plop\\Stub\\HandlerAbstract',
            array('getStderr', 'emit')
        );
        $handler
            ->expects($this->once())
            ->method('getStderr')
            ->will($this->returnValue($this->stderrStream));

        $this->expectStderrRegex(
            "#exception 'Plop\\\\Exception' with message" .
            " 'test' in [^\\r\\n]+:$line(\\r\\n?|\\n).*#m"
        );
        $this->assertSame(
            $handler,
            $handler->handleError($this->record, $exc)
        );
    }
}
