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

class Stream extends \Plop_TestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->handler = $this->getMock(
            '\\Plop\\Stub\\Handler\\Stream',
            array('format'),
            array($this->stderrStream, 'ISO-8859-1')
        );
        $this->record = $this->getMock('\\Plop\\Stub\\RecordInterface');
    }

    public function tearDown()
    {
        unset($this->handler);
        parent::tearDown();
    }

    /**
     * @covers \Plop\Handler\Stream::__construct
     * @covers \Plop\Handler\Stream::emit
     * @covers \Plop\Handler\Stream::flush
     */
    public function testLogging()
    {
        $this->handler
            ->expects($this->once())
            ->method('format')
            ->with($this->record)
            ->will($this->returnValue('abc'));
        $this->handler->emitStub($this->record);
        $this->expectStderrString("abc\n");
    }
}
