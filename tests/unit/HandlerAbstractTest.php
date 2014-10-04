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

class Test extends \Plop_TestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->record       = $this->getMock('\\Plop\\Stub\\RecordInterface');
        $this->formatter    = $this->getMock('\\Plop\\FormatterInterface');
    }

    /**
     * @covers \Plop\HandlerAbstract::__construct
     */
    public function testDefaultArguments()
    {
        $handler = $this->getMock(
            '\\Plop\\Stub\\HandlerAbstract',
            array('emit'),
            array()
        );
        $formatter = $handler->getFormatter();
        $this->assertSame('Plop\\Formatter', get_class($formatter));
    }

    /**
     * @covers \Plop\HandlerAbstract::__construct
     */
    public function testDefaultArgumentsOverride()
    {
        $handler = $this->getMock(
            '\\Plop\\Stub\\HandlerAbstract',
            array('emit'),
            array($this->formatter)
        );
        $this->assertSame($this->formatter, $handler->getFormatter());
    }

    /**
     * @covers \Plop\HandlerAbstract::handle
     */
    public function testHandleMethod()
    {
        $handler = $this->getMock(
            '\\Plop\\Stub\\HandlerAbstract',
            array('format', 'emit')
        );
        $handler
            ->expects($this->once())
            ->method('format')
            ->with($this->record)
            ->will($this->returnValue('Foo'));
        $handler
            ->expects($this->once())
            ->method('emit')
            ->with($this->record);
        $this->assertSame($handler, $handler->handle($this->record));
    }
}
