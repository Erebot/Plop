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
    DIRECTORY_SEPARATOR . 'TimedRotatingFile.php'
);

class   Plop_Handler_TimedRotatingFile_Test
extends Plop_TestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->_handler = $this->getMock(
            'Plop_Handler_TimedRotatingFile_Stub',
            array(
                '_computeRollover',
                '_shouldRollover',
                '_getFilesToDelete',
                '_doRollover',
                '_open',
                '_getTime',
            ),
            array(),
            '',
            FALSE
        );
        $this->_handler
            ->expects($this->once())
            ->method('_open')
            ->will($this->returnValue($this->stderrStream));
        $this->_record  = $this->getMock('Plop_RecordInterface');
    }

    public function tearDown()
    {
        unset($this->_handler);
        parent::tearDown();
    }

    /**
     * @covers Plop_Handler_TimedRotatingFile::__construct
     */
    public function testConstructWithDefaultArguments()
    {
        $this->_handler->__construct('php://stderr');
        $this->assertSame('H',      $this->_handler->getWhenStub());
        $this->assertSame(0,        $this->_handler->getBackupCountStub());
        $this->assertSame(FALSE,    $this->_handler->getUTCStub());
        $this->assertSame(60 * 60,  $this->_handler->getIntervalStub());
        $this->assertSame(
            '%Y-%m-%d_%H',
            $this->_handler->getSuffixStub()
        );
        $this->assertSame(
            '^\\d{4}-\\d{2}-\\d{2}_\\d{2}$',
            $this->_handler->getExtMatchStub()
        );
        $this->assertSame(NULL,     $this->_handler->getDayOfWeekStub());
    }

    /**
     * @covers                      Plop_Handler_TimedRotatingFile::__construct
     * @expectedException           Plop_Exception
     * @expectedExceptionMessage    The interval should be an integer greater than or equal to 1
     */
    public function testConstructWithInvalidInterval()
    {
        // Only integers are accepted.
        $this->_handler->__construct('php://stderr', 'h', 0.5);
    }

    /**
     * @covers                      Plop_Handler_TimedRotatingFile::__construct
     * @expectedException           Plop_Exception
     * @expectedExceptionMessage    The interval should be an integer greater than or equal to 1
     */
    public function testConstructWithInvalidInterval2()
    {
        // The interval may not be less than 1.
        $this->_handler->__construct('php://stderr', 'h', 0);
    }

    /**
     * @covers                      Plop_Handler_TimedRotatingFile::__construct
     * @expectedException           Plop_Exception
     * @expectedExceptionMessage    Invalid rollover interval specified: ABC
     */
    public function testConstructWithInvalidSpecification()
    {
        // The interval may not be less than 1.
        $this->_handler->__construct('php://stderr', 'abc');
    }

    /**
     * @covers Plop_Handler_TimedRotatingFile::__construct
     */
    public function testConstructWithSeconds()
    {
        $this->_handler->__construct('php://stderr', 's', 42);
        $this->assertSame('S',      $this->_handler->getWhenStub());
        $this->assertSame(0,        $this->_handler->getBackupCountStub());
        $this->assertSame(FALSE,    $this->_handler->getUTCStub());
        $this->assertSame(42,       $this->_handler->getIntervalStub());
        $this->assertSame(
            '%Y-%m-%d_%H-%M-%S',
            $this->_handler->getSuffixStub()
        );
        $this->assertSame(
            '^\\d{4}-\\d{2}-\\d{2}_\\d{2}-\\d{2}-\\d{2}$',
            $this->_handler->getExtMatchStub()
        );
        $this->assertSame(NULL, $this->_handler->getDayOfWeekStub());
    }

    /**
     * @covers Plop_Handler_TimedRotatingFile::__construct
     */
    public function testConstructWithMinutes()
    {
        $this->_handler->__construct('php://stderr', 'm', 42);
        $this->assertSame('M',      $this->_handler->getWhenStub());
        $this->assertSame(0,        $this->_handler->getBackupCountStub());
        $this->assertSame(FALSE,    $this->_handler->getUTCStub());
        $this->assertSame(42 * 60,  $this->_handler->getIntervalStub());
        $this->assertSame(
            '%Y-%m-%d_%H-%M',
            $this->_handler->getSuffixStub()
        );
        $this->assertSame(
            '^\\d{4}-\\d{2}-\\d{2}_\\d{2}-\\d{2}$',
            $this->_handler->getExtMatchStub()
        );
        $this->assertSame(NULL, $this->_handler->getDayOfWeekStub());
    }

    /**
     * @covers Plop_Handler_TimedRotatingFile::__construct
     */
    public function testConstructWithHours()
    {
        $this->_handler->__construct('php://stderr', 'h', 42);
        $this->assertSame('H',          $this->_handler->getWhenStub());
        $this->assertSame(0,            $this->_handler->getBackupCountStub());
        $this->assertSame(FALSE,        $this->_handler->getUTCStub());
        $this->assertSame(42 * 3600,    $this->_handler->getIntervalStub());
        $this->assertSame(
            '%Y-%m-%d_%H',
            $this->_handler->getSuffixStub()
        );
        $this->assertSame(
            '^\\d{4}-\\d{2}-\\d{2}_\\d{2}$',
            $this->_handler->getExtMatchStub()
        );
        $this->assertSame(NULL, $this->_handler->getDayOfWeekStub());
    }

    /**
     * @covers Plop_Handler_TimedRotatingFile::__construct
     */
    public function testConstructWithDays()
    {
        $this->_handler->__construct('php://stderr', 'd', 42);
        $this->assertSame('D',          $this->_handler->getWhenStub());
        $this->assertSame(0,            $this->_handler->getBackupCountStub());
        $this->assertSame(FALSE,        $this->_handler->getUTCStub());
        $this->assertSame(42 * 86400,   $this->_handler->getIntervalStub());
        $this->assertSame(
            '%Y-%m-%d',
            $this->_handler->getSuffixStub()
        );
        $this->assertSame(
            '^\\d{4}-\\d{2}-\\d{2}$',
            $this->_handler->getExtMatchStub()
        );
        $this->assertSame(NULL, $this->_handler->getDayOfWeekStub());
    }

    /**
     * @covers Plop_Handler_TimedRotatingFile::__construct
     */
    public function testConstructWithWeeks()
    {
        $this->_handler->__construct('php://stderr', 'w6', 42);
        $this->assertSame('W6',          $this->_handler->getWhenStub());
        $this->assertSame(0,            $this->_handler->getBackupCountStub());
        $this->assertSame(FALSE,        $this->_handler->getUTCStub());
        $this->assertSame(42 * 604800,  $this->_handler->getIntervalStub());
        $this->assertSame(
            '%Y-%m-%d',
            $this->_handler->getSuffixStub()
        );
        $this->assertSame(
            '^\\d{4}-\\d{2}-\\d{2}$',
            $this->_handler->getExtMatchStub()
        );
        $this->assertSame(6, $this->_handler->getDayOfWeekStub());
    }

    /**
     * @covers                      Plop_Handler_TimedRotatingFile::__construct
     * @expectedException           Plop_Exception
     * @expectedExceptionMessage    You must specify a day for weekly rollover from 0 to 6 (0 is Monday), not WABC

     */
    public function testConstructWithInvalidDay()
    {
        $this->_handler->__construct('php://stderr', 'wabc', 42);
    }

    /**
     * @covers                      Plop_Handler_TimedRotatingFile::__construct
     * @expectedException           Plop_Exception
     * @expectedExceptionMessage    Invalid day specified for weekly rollover: 7
     */
    public function testConstructWithInvalidDay2()
    {
        $this->_handler->__construct('php://stderr', 'w7', 42);
    }

    /**
     * @covers Plop_Handler_TimedRotatingFile::__construct
     */
    public function testConstructWithOverridenParameters()
    {
        $this->_handler->__construct(
            'php://stderr',
            'midnight',
            42,
            5,
            FALSE,
            TRUE
        );
        $this->assertSame('MIDNIGHT',   $this->_handler->getWhenStub());
        $this->assertSame(5,            $this->_handler->getBackupCountStub());
        $this->assertSame(TRUE,         $this->_handler->getUTCStub());
        $this->assertSame(42 * 86400,   $this->_handler->getIntervalStub());
        $this->assertSame(
            '%Y-%m-%d',
            $this->_handler->getSuffixStub()
        );
        $this->assertSame(
            '^\\d{4}-\\d{2}-\\d{2}$',
            $this->_handler->getExtMatchStub()
        );
        $this->assertSame(NULL, $this->_handler->getDayOfWeekStub());
    }

    /**
     * @covers Plop_Handler_TimedRotatingFile::_computeRollover
     */
    public function testRolloverComputation()
    {
        $this->_handler->__construct('php://stderr', 'midnight');
        $this->assertSame('MIDNIGHT', $this->_handler->getWhenStub());
        $this->assertSame(
            // Tue Oct 23 00:00:00 CEST 2012
            1350950400,
            // Mon Oct 22 23:33:19 CEST 2012
            $this->_handler->computeRolloverStub(1350941599)
        );
    }

    /**
     * @covers Plop_Handler_TimedRotatingFile::_computeRollover
     */
    public function testRolloverComputation2()
    {
        $this->_handler->__construct('php://stderr', 'w2');
        $this->assertSame('W2', $this->_handler->getWhenStub());
        $this->assertSame(
            // Wed Oct 24 00:00:00 CEST 2012
            1351036800,
            // Mon Oct 22 23:33:19 CEST 2012
            $this->_handler->computeRolloverStub(1350941599)
        );
    }

    /**
     * @covers Plop_Handler_TimedRotatingFile::_computeRollover
     */
    public function testRolloverComputation3()
    {
        // 1h by default
        $this->_handler->__construct('php://stderr');
        $this->assertSame('H',  $this->_handler->getWhenStub());
        $this->assertSame(3600, $this->_handler->getIntervalStub());
        $this->assertSame(
            // Tue Oct 23 00:33:19 CEST 2012
            1350945199,
            // Mon Oct 22 23:33:19 CEST 2012
            $this->_handler->computeRolloverStub(1350941599)
        );
    }

    /**
     * @covers Plop_Handler_TimedRotatingFile::_shouldRollover
     */
    public function testRolloverDecision()
    {
        $time = time();
        $this->_handler
            ->expects($this->exactly(2))
            ->method('_getTime')
            ->will($this->onConsecutiveCalls($time, $time));
        $this->_handler
            ->expects($this->once())
            ->method('_computeRollover')
            ->will($this->returnArgument(0));
        $this->_handler->__construct('php://stderr');
        $this->assertTrue($this->_handler->shouldRolloverStub($this->_record));
    }

    /**
     * @covers Plop_Handler_TimedRotatingFile::_shouldRollover
     */
    public function testRolloverDecision2()
    {
        $time = time();
        $this->_handler
            ->expects($this->exactly(2))
            ->method('_getTime')
            ->will($this->onConsecutiveCalls($time, $time - 1));
        $this->_handler
            ->expects($this->once())
            ->method('_computeRollover')
            ->will($this->returnArgument(0));
        $this->_handler->__construct('php://stderr');
        $this->assertFalse($this->_handler->shouldRolloverStub($this->_record));
    }
}
