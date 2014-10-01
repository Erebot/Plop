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

namespace Plop\Tests;

class Formatter extends \Plop_TestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->record       = $this->getMock('\\Plop\\Stub\\RecordInterface');
        $this->formatter    = new \Plop\Stub\Formatter();
    }

    /**
     * @covers \Plop\Formatter::__construct
     */
    public function testDefaultArguments()
    {
        $this->assertSame(
            \Plop\Formatter::DEFAULT_FORMAT,
            $this->formatter->getFormat()
        );
        $this->assertSame(
            \Plop\Formatter::DEFAULT_DATE_FORMAT,
            $this->formatter->getDateFormat()
        );
        $this->assertSame(null, $this->formatter->getTimezone());
        $this->assertFalse($this->formatter->getPythonLike());
    }

    /**
     * @covers \Plop\Formatter::__construct
     */
    public function testDefaultArgumentsOverride()
    {
        $timezone = $this->getMock(
            '\\DateTimeZone',
            array(),
            array(),
            '',
            false
        );

        $formatter = new \Plop\Formatter('%(asctime)s', 'U', $timezone, true);
        $this->assertSame('%(asctime)s', $formatter->getFormat());
        $this->assertSame('U', $formatter->getDateFormat());
        $this->assertSame($timezone, $formatter->getTimezone());
        $this->assertTrue($formatter->getPythonLike());
    }

    /**
     * @covers \Plop\Formatter::getFormat
     * @covers \Plop\Formatter::setFormat
     */
    public function testFormatAccessors()
    {
        $newFormat = '%(asctime)s';
        $this->assertNotEquals(\Plop\Formatter::DEFAULT_FORMAT, $newFormat);
        $this->assertSame(
            \Plop\Formatter::DEFAULT_FORMAT,
            $this->formatter->getFormat()
        );
        $this->assertSame(
            $this->formatter,
            $this->formatter->setFormat($newFormat)
        );
        $this->assertSame($newFormat, $this->formatter->getFormat());
    }

    /**
     * @covers \Plop\Formatter::getDateFormat
     * @covers \Plop\Formatter::setDateFormat
     */
    public function testDateFormatAccessors()
    {
        $newFormat = 'U';
        $this->assertNotEquals(\Plop\Formatter::DEFAULT_DATE_FORMAT, $newFormat);
        $this->assertSame(
            \Plop\Formatter::DEFAULT_DATE_FORMAT,
            $this->formatter->getDateFormat()
        );
        $this->assertSame(
            $this->formatter,
            $this->formatter->setDateFormat($newFormat)
        );
        $this->assertSame($newFormat, $this->formatter->getDateFormat());
    }

    /**
     * @covers \Plop\Formatter::getPythonLike
     * @covers \Plop\Formatter::setPythonLike
     */
    public function testPythonStackTracesAccessors()
    {
        $this->assertFalse($this->formatter->getPythonLike());
        $this->assertSame(
            $this->formatter,
            $this->formatter->setPythonLike(true)
        );
        $this->assertTrue($this->formatter->getPythonLike());
    }

    /**
     * @covers                      \Plop\Formatter::setPythonLike
     * @expectedException           \Plop\Exception
     * @expectedExceptionMessage    Invalid value
     */
    public function testPythonStackTracesAccessors2()
    {
        $this->formatter->setPythonLike('foo');
    }

    /**
     * @covers \Plop\Formatter::formatTime
     */
    public function testFormatTime()
    {
        $date = $this->getMock('\\DateTime');
        $date
            ->expects($this->once())
            ->method('format')
            ->with(\Plop\Formatter::DEFAULT_DATE_FORMAT)
            ->will($this->returnValue('ok'));
        $this->record
            ->expects($this->once())
            ->method('offsetGet')
            ->with('createdDate')
            ->will($this->returnValue($date));
        $this->assertSame(
            'ok',
            $this->formatter->formatTimeStub($this->record)
        );
    }

    /**
     * @covers \Plop\Formatter::formatTime
     */
    public function testFormatTime2()
    {
        $date = $this->getMock('\\DateTime');
        $date
            ->expects($this->once())
            ->method('format')
            ->with('U')
            ->will($this->returnValue('ok'));
        $this->record
            ->expects($this->once())
            ->method('offsetGet')
            ->with('createdDate')
            ->will($this->returnValue($date));
        $this->assertSame(
            'ok',
            $this->formatter->formatTimeStub($this->record, 'U')
        );
    }

    /**
     * @covers \Plop\Formatter::formatException
     */
    public function testFormatException()
    {
        $exc    = new \Plop\Exception('Foo');
        $line   = __LINE__ - 1;
        $file   = __FILE__;
        $msg    = $this->formatter->formatExceptionStub($exc);
        $msg    = substr($msg, 0, strcspn($msg, "\r\n"));
        $this->assertSame(
            "exception 'Plop\\Exception' with message 'Foo' in $file:$line",
            $msg
        );
    }

    /**
     * @covers \Plop\Formatter::formatException
     */
    public function testFormatException2()
    {
        $this->formatter->setPythonLike(true);
        $exc    = new \Plop\Exception('Foo');
        $line   = __LINE__ - 1;
        $file   = __FILE__;
        $msg    = $this->formatter->formatExceptionStub($exc);
        $msg1   = substr($msg, 0, strcspn($msg, "\r\n"));
        $msg2   = substr(strrchr($msg, "\n"), 1);
        $this->assertSame('Traceback (most recent call last):', $msg1);
        $this->assertSame(
            "Exception 'Plop\\Exception' with message 'Foo' in $file:$line",
            $msg2
        );
    }

    /**
     * @covers \Plop\Formatter::format
     */
    public function testFormatMethod()
    {
        $formatter = $this->getMock(
            '\\Plop\\Formatter',
            array(
                'formatException',
                'formatTime',
            )
        );
        $formatter = new \Plop\Formatter();

        $interpolator   = $this->getMock('\\Plop\\Interpolator\\Percent');
        $interpolator   = new \Plop\Interpolator\Percent();
        $exc            = new \Plop\Exception('Foo');
        $epoch          = new \DateTime('@0', new \DateTimeZone('UTC'));

        $formatter
            ->setFormat('%(asctime)s - %(message)s')
            ->setDateFormat('Y-m-d')
            ->setInterpolator($interpolator);

        $this->record
            ->expects($this->once())
            ->method('getMessage')
            ->will($this->returnValue('Foo'));

        $this->record
            ->expects($this->once())
            ->method('asArray')
            ->will(
                $this->returnValue(
                    array(
                        'asctime' => '1970-01-01',
                        'message' => 'Foo',
                    )
                )
            );

#        $formatter
#            ->expects($this->once())
#            ->method('formatTime')
#            ->with($this->record, \Plop\Formatter::DEFAULT_DATE_FORMAT)
#            ->will($this->returnValue('Baz'));

#        $formatter
#            ->expects($this->once())
#            ->method('formatException')
#            ->with($exc)
#            ->will($this->returnValue('Qux'));

        $this->setExpectations(
            $this->record,
            'offsetSet',
            array(
                array('/message/', '/Foo/'),
                array('/asctime/', '/1970-01-01/'),
                array('/exc_text/', '/.*/'),
            ),
            array(null, null, null),
            true
        );

        $this->setExpectations(
            $this->record,
            'offsetGet',
            array(
                array('createdDate'),
                array('exc_info'),
                array('exc_text'),
                array('exc_info'),
                array('exc_text'),
                array('exc_text'),
            ),
            array(
                $epoch,
                $exc,
                false,
                $exc,
                'Plop\\Exception',
                'Plop\\Exception',
            )
        );

        $this->assertSame(
            "1970-01-01 - Foo\nPlop\\Exception",
            $formatter->format($this->record)
        );
    }
}
