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

class TimedRotatingFile extends \Plop_TestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->handler = $this->getMock(
            '\\Plop\\Stub\\Handler\\TimedRotatingFile',
            array(
                'computeRollover',
                'shouldRollover',
                'getFilesToDelete',
                'doRollover',
                'open',
                'getTime',
            ),
            array(),
            '',
            false
        );
        $this->handler
            ->expects($this->once())
            ->method('open')
            ->will($this->returnValue($this->stderrStream));
        $this->record  = $this->getMock('\\Plop\\Stub\\RecordInterface');
    }

    public function tearDown()
    {
        unset($this->handler);
        parent::tearDown();
    }

    /**
     * @covers \Plop\Handler\TimedRotatingFile::__construct
     */
    public function testConstructWithDefaultArguments()
    {
        $this->handler->__construct('php://stderr');
        $this->assertSame('H', $this->handler->getWhenStub());
        $this->assertSame(0, $this->handler->getBackupCountStub());
        $this->assertSame(false, $this->handler->getUTCStub());
        $this->assertSame(60 * 60, $this->handler->getIntervalStub());
        $this->assertSame(
            '%Y-%m-%d_%H',
            $this->handler->getSuffixStub()
        );
        $this->assertSame(
            '^\\d{4}-\\d{2}-\\d{2}_\\d{2}$',
            $this->handler->getExtMatchStub()
        );
        $this->assertSame(null, $this->handler->getDayOfWeekStub());
    }

    /**
     * @covers                      \Plop\Handler\TimedRotatingFile::__construct
     * @expectedException           \Plop\Exception
     * @expectedExceptionMessage    The interval should be an integer greater than or equal to 1
     */
    public function testConstructWithInvalidInterval()
    {
        // Only integers are accepted.
        $this->handler->__construct('php://stderr', 'h', 0.5);
    }

    /**
     * @covers                      \Plop\Handler\TimedRotatingFile::__construct
     * @expectedException           \Plop\Exception
     * @expectedExceptionMessage    The interval should be an integer greater than or equal to 1
     */
    public function testConstructWithInvalidInterval2()
    {
        // The interval may not be less than 1.
        $this->handler->__construct('php://stderr', 'h', 0);
    }

    /**
     * @covers                      \Plop\Handler\TimedRotatingFile::__construct
     * @expectedException           \Plop\Exception
     * @expectedExceptionMessage    Invalid rollover interval specified: ABC
     */
    public function testConstructWithInvalidSpecification()
    {
        // The interval may not be less than 1.
        $this->handler->__construct('php://stderr', 'abc');
    }

    /**
     * @covers \Plop\Handler\TimedRotatingFile::__construct
     */
    public function testConstructWithSeconds()
    {
        $this->handler->__construct('php://stderr', 's', 42);
        $this->assertSame('S', $this->handler->getWhenStub());
        $this->assertSame(0, $this->handler->getBackupCountStub());
        $this->assertSame(false, $this->handler->getUTCStub());
        $this->assertSame(42, $this->handler->getIntervalStub());
        $this->assertSame('%Y-%m-%d_%H-%M-%S', $this->handler->getSuffixStub());
        $this->assertSame('^\\d{4}-\\d{2}-\\d{2}_\\d{2}-\\d{2}-\\d{2}$', $this->handler->getExtMatchStub());
        $this->assertSame(null, $this->handler->getDayOfWeekStub());
    }

    /**
     * @covers \Plop\Handler\TimedRotatingFile::__construct
     */
    public function testConstructWithMinutes()
    {
        $this->handler->__construct('php://stderr', 'm', 42);
        $this->assertSame('M', $this->handler->getWhenStub());
        $this->assertSame(0, $this->handler->getBackupCountStub());
        $this->assertSame(false, $this->handler->getUTCStub());
        $this->assertSame(42 * 60, $this->handler->getIntervalStub());
        $this->assertSame('%Y-%m-%d_%H-%M', $this->handler->getSuffixStub());
        $this->assertSame('^\\d{4}-\\d{2}-\\d{2}_\\d{2}-\\d{2}$', $this->handler->getExtMatchStub());
        $this->assertSame(null, $this->handler->getDayOfWeekStub());
    }

    /**
     * @covers \Plop\Handler\TimedRotatingFile::__construct
     */
    public function testConstructWithHours()
    {
        $this->handler->__construct('php://stderr', 'h', 42);
        $this->assertSame('H', $this->handler->getWhenStub());
        $this->assertSame(0, $this->handler->getBackupCountStub());
        $this->assertSame(false, $this->handler->getUTCStub());
        $this->assertSame(42 * 3600, $this->handler->getIntervalStub());
        $this->assertSame('%Y-%m-%d_%H', $this->handler->getSuffixStub());
        $this->assertSame('^\\d{4}-\\d{2}-\\d{2}_\\d{2}$', $this->handler->getExtMatchStub());
        $this->assertSame(null, $this->handler->getDayOfWeekStub());
    }

    /**
     * @covers \Plop\Handler\TimedRotatingFile::__construct
     */
    public function testConstructWithDays()
    {
        $this->handler->__construct('php://stderr', 'd', 42);
        $this->assertSame('D', $this->handler->getWhenStub());
        $this->assertSame(0, $this->handler->getBackupCountStub());
        $this->assertSame(false, $this->handler->getUTCStub());
        $this->assertSame(42 * 86400, $this->handler->getIntervalStub());
        $this->assertSame('%Y-%m-%d', $this->handler->getSuffixStub());
        $this->assertSame('^\\d{4}-\\d{2}-\\d{2}$', $this->handler->getExtMatchStub());
        $this->assertSame(null, $this->handler->getDayOfWeekStub());
    }

    /**
     * @covers \Plop\Handler\TimedRotatingFile::__construct
     */
    public function testConstructWithWeeks()
    {
        $this->handler->__construct('php://stderr', 'w6', 42);
        $this->assertSame('W6', $this->handler->getWhenStub());
        $this->assertSame(0, $this->handler->getBackupCountStub());
        $this->assertSame(false, $this->handler->getUTCStub());
        $this->assertSame(42 * 604800, $this->handler->getIntervalStub());
        $this->assertSame('%Y-%m-%d', $this->handler->getSuffixStub());
        $this->assertSame('^\\d{4}-\\d{2}-\\d{2}$', $this->handler->getExtMatchStub());
        $this->assertSame(6, $this->handler->getDayOfWeekStub());
    }

    /**
     * @covers                      \Plop\Handler\TimedRotatingFile::__construct
     * @expectedException           \Plop\Exception
     * @expectedExceptionMessage    You must specify a day for weekly rollover from 0 to 6 (0 is Monday), not WABC

     */
    public function testConstructWithInvalidDay()
    {
        $this->handler->__construct('php://stderr', 'wabc', 42);
    }

    /**
     * @covers                      \Plop\Handler\TimedRotatingFile::__construct
     * @expectedException           \Plop\Exception
     * @expectedExceptionMessage    Invalid day specified for weekly rollover: 7
     */
    public function testConstructWithInvalidDay2()
    {
        $this->handler->__construct('php://stderr', 'w7', 42);
    }

    /**
     * @covers \Plop\Handler\TimedRotatingFile::__construct
     */
    public function testConstructWithOverridenParameters()
    {
        $this->handler->__construct(
            'php://stderr',
            'midnight',
            42,
            5,
            false,
            true
        );
        $this->assertSame('MIDNIGHT', $this->handler->getWhenStub());
        $this->assertSame(5, $this->handler->getBackupCountStub());
        $this->assertSame(true, $this->handler->getUTCStub());
        $this->assertSame(42 * 86400, $this->handler->getIntervalStub());
        $this->assertSame('%Y-%m-%d', $this->handler->getSuffixStub());
        $this->assertSame('^\\d{4}-\\d{2}-\\d{2}$', $this->handler->getExtMatchStub());
        $this->assertSame(null, $this->handler->getDayOfWeekStub());
    }

    /**
     * @covers \Plop\Handler\TimedRotatingFile::computeRollover
     */
    public function testRolloverComputation()
    {
        $this->handler->__construct('php://stderr', 'midnight');
        $this->assertSame('MIDNIGHT', $this->handler->getWhenStub());
        $this->assertSame(
            // Tue Oct 23 00:00:00 CEST 2012
            1350950400,
            // Mon Oct 22 23:33:19 CEST 2012
            $this->handler->computeRolloverStub(1350941599)
        );
    }

    /**
     * @covers \Plop\Handler\TimedRotatingFile::computeRollover
     */
    public function testRolloverComputation2()
    {
        $this->handler->__construct('php://stderr', 'w2');
        $this->assertSame('W2', $this->handler->getWhenStub());
        $this->assertSame(
            // Wed Oct 24 00:00:00 CEST 2012
            1351036800,
            // Mon Oct 22 23:33:19 CEST 2012
            $this->handler->computeRolloverStub(1350941599)
        );
    }

    /**
     * @covers \Plop\Handler\TimedRotatingFile::computeRollover
     */
    public function testRolloverComputation3()
    {
        // 1h by default
        $this->handler->__construct('php://stderr');
        $this->assertSame('H', $this->handler->getWhenStub());
        $this->assertSame(3600, $this->handler->getIntervalStub());
        $this->assertSame(
            // Tue Oct 23 00:33:19 CEST 2012
            1350945199,
            // Mon Oct 22 23:33:19 CEST 2012
            $this->handler->computeRolloverStub(1350941599)
        );
    }

    /**
     * @covers \Plop\Handler\TimedRotatingFile::shouldRollover
     */
    public function testRolloverDecision()
    {
        $time = time();
        $this->handler
            ->expects($this->exactly(2))
            ->method('getTime')
            ->will($this->onConsecutiveCalls($time, $time));
        $this->handler
            ->expects($this->once())
            ->method('computeRollover')
            ->will($this->returnArgument(0));
        $this->handler->__construct('php://stderr');
        $this->assertTrue($this->handler->shouldRolloverStub($this->record));
    }

    /**
     * @covers \Plop\Handler\TimedRotatingFile::shouldRollover
     */
    public function testRolloverDecision2()
    {
        $time = time();
        $this->handler
            ->expects($this->exactly(2))
            ->method('getTime')
            ->will($this->onConsecutiveCalls($time, $time - 1));
        $this->handler
            ->expects($this->once())
            ->method('computeRollover')
            ->will($this->returnArgument(0));
        $this->handler->__construct('php://stderr');
        $this->assertFalse($this->handler->shouldRolloverStub($this->record));
    }
}
