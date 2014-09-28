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

namespace Plop\Tests\Handler;

class File extends \Plop_TestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->handler = $this->getMock(
            '\\Plop\\Stub\\Handler\\File',
            array('open', 'close', 'flush', 'format'),
            array(),
            '',
            false
        );
        $this->record = $this->getMock('\\Plop\\Stub\\RecordInterface');
    }

    public function tearDown()
    {
        unset($this->handler);
        parent::tearDown();
    }

    /**
     * @covers \Plop\Handler\File::__construct
     * @covers \Plop\Handler\File::close
     */
    public function testImmediateOpeningAndClosing()
    {
        $this->handler
            ->expects($this->once())
            ->method('open')
            ->will($this->returnValue($this->stderrStream));
        $this->handler->__construct('php://stderr');
        $this->assertSame(
            $this->stderrStream,
            $this->handler->getStreamStub()
        );

        $this->handler->closeStub();
        $this->assertFalse($this->handler->getStreamStub());
    }

    /**
     * @covers \Plop\Handler\File::__construct
     * @covers \Plop\Handler\File::__destruct
     */
    public function testDelayedOpening()
    {
        $this->handler
            ->expects($this->never())
            ->method('open');
        $this->handler->__construct('php://stderr', 'at', true);
    }

    /**
     * @covers \Plop\Handler\File::open
     */
    public function testFileOpening()
    {
        $this->handler->__construct('php://stderr', 'a+b', true);
        $this->assertFalse($this->handler->getStreamStub());
        $stream = $this->handler->openStub();
        $this->assertNotSame(false, $stream);

        $metadata = stream_get_meta_data($stream);
        $this->assertSame('a+b', $metadata['mode']);
    }

    /**
     * @covers \Plop\Handler\File::emit
     */
    public function testEmitMethod()
    {
        $this->handler
            ->expects($this->once())
            ->method('open')
            ->will($this->returnValue($this->stderrStream));
        $this->handler
            ->expects($this->once())
            ->method('format')
            ->will($this->returnValue('abc'));
        $this->expectStderrString("abc\n");
        $this->handler->__construct('php://stderr', 'at', true);
        $this->assertFalse($this->handler->getStreamStub());

        $this->handler->emitStub($this->record);
        $this->assertSame(
            $this->stderrStream,
            $this->handler->getStreamStub()
        );
    }
}
