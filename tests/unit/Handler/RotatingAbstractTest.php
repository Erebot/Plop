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

class RotatingAbstract extends \Plop_TestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->handler = $this->getMock(
            '\\Plop\\Stub\\Handler\\RotatingAbstract',
            array(
                'doRollover',
                'shouldRollover',
                'handleError',
                'open',
                'format',
            ),
            array(''),
            '',
            false
        );
        $this->record  = $this->getMock('\\Plop\\Stub\\RecordInterface');

        $this->handler
            ->expects($this->once())
            ->method('open')
            ->will($this->returnValue($this->stderrStream));
        $this->handler->__construct('');
    }

    public function tearDown()
    {
        unset($this->handler);
        parent::tearDown();
    }

    /**
     * @cover \Plop\Handler\RotatingAbstract::emit
     */
    public function testEmitWithoutRollover()
    {
        $this->handler
            ->expects($this->once())
            ->method('format')
            ->with($this->record)
            ->will($this->returnValue('test'));
        $this->handler
            ->expects($this->once())
            ->method('shouldRollover')
            ->with($this->record)
            ->will($this->returnValue(false));
        $this->handler
            ->expects($this->never())
            ->method('doRollover');
        $this->handler
            ->expects($this->never())
            ->method('handleError');
        $this->handler->emitStub($this->record);
        $this->expectStderrString("test\n");
    }

    /**
     * @cover \Plop\Handler\RotatingAbstract::emit
     */
    public function testEmitWithRollover()
    {
        $this->handler
            ->expects($this->once())
            ->method('format')
            ->with($this->record)
            ->will($this->returnValue('test'));
        $this->handler
            ->expects($this->once())
            ->method('shouldRollover')
            ->with($this->record)
            ->will($this->returnValue(true));
        $this->handler
            ->expects($this->once())
            ->method('doRollover');
        $this->handler
            ->expects($this->never())
            ->method('handleError');
        $this->handler->emitStub($this->record);
        $this->expectStderrString("test\n");
    }

    /**
     * @cover \Plop\Handler\RotatingAbstract::emit
     */
    public function testEmitThrowingException()
    {
        $exc = new \Plop\Exception('');
        $this->handler
            ->expects($this->once())
            ->method('shouldRollover')
            ->with($this->record)
            ->will($this->throwException($exc));
        $this->handler
            ->expects($this->never())
            ->method('doRollover');
        $this->handler
            ->expects($this->once())
            ->method('handleError')
            ->with($this->record, $exc);
        $this->handler->emitStub($this->record);
    }
}
