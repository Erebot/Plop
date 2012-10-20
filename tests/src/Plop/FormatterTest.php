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
    DIRECTORY_SEPARATOR . 'Formatter.php'
);

class   Plop_Formatter_Test
extends Plop_TestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->_record      = $this->getMock('Plop_RecordInterface');
        $this->_formatter   = new Plop_Formatter_Stub();
    }

    /**
     * @covers Plop_Formatter::__construct
     */
    public function testDefaultArguments()
    {
        $this->assertSame(
            Plop_Formatter::DEFAULT_FORMAT,
            $this->_formatter->getFormat()
        );
        $this->assertSame(
            Plop_Formatter::DEFAULT_DATE_FORMAT,
            $this->_formatter->getDateFormat()
        );
        $this->assertFalse($this->_formatter->getPythonLike());
    }

    /**
     * @covers Plop_Formatter::__construct
     */
    public function testDefaultArgumentsOverride()
    {
        $formatter = new Plop_Formatter('%(asctime)s', 'U', TRUE);
        $this->assertSame('%(asctime)s', $formatter->getFormat());
        $this->assertSame('U', $formatter->getDateFormat());
        $this->assertTrue($formatter->getPythonLike());
    }

    /**
     * @covers Plop_Formatter::getFormat
     * @covers Plop_Formatter::setFormat
     */
    public function testFormatAccessors()
    {
        $newFormat = '%(asctime)s';
        $this->assertNotEquals(Plop_Formatter::DEFAULT_FORMAT, $newFormat);
        $this->assertSame(
            Plop_Formatter::DEFAULT_FORMAT,
            $this->_formatter->getFormat()
        );
        $this->assertSame(
            $this->_formatter,
            $this->_formatter->setFormat($newFormat)
        );
        $this->assertSame($newFormat, $this->_formatter->getFormat());
    }

    /**
     * @covers Plop_Formatter::getDateFormat
     * @covers Plop_Formatter::setDateFormat
     */
    public function testDateFormatAccessors()
    {
        $newFormat = 'U';
        $this->assertNotEquals(Plop_Formatter::DEFAULT_DATE_FORMAT, $newFormat);
        $this->assertSame(
            Plop_Formatter::DEFAULT_DATE_FORMAT,
            $this->_formatter->getDateFormat()
        );
        $this->assertSame(
            $this->_formatter,
            $this->_formatter->setDateFormat($newFormat)
        );
        $this->assertSame($newFormat, $this->_formatter->getDateFormat());
    }

    /**
     * @covers Plop_Formatter::getPythonLike
     * @covers Plop_Formatter::setPythonLike
     */
    public function testPythonStackTracesAccessors()
    {
        $this->assertFalse($this->_formatter->getPythonLike());
        $this->assertSame(
            $this->_formatter,
            $this->_formatter->setPythonLike(TRUE)
        );
        $this->assertTrue($this->_formatter->getPythonLike());
    }

    /**
     * @covers                      Plop_Formatter::setPythonLike
     * @expectedException           Plop_Exception
     * @expectedExceptionMessage    Invalid value
     */
    public function testPythonStackTracesAccessors2()
    {
        $this->_formatter->setPythonLike('foo');
    }

    /**
     * @covers Plop_Formatter::_formatTime
     */
    public function testFormatTime()
    {
        $date = $this->getMock('DateTime');
        $date
            ->expects($this->once())
            ->method('format')
            ->with(Plop_Formatter::DEFAULT_DATE_FORMAT)
            ->will($this->returnValue('ok'));
        $this->_record
            ->expects($this->once())
            ->method('offsetGet')
            ->with('createdDate')
            ->will($this->returnValue($date));
        $this->assertSame(
            'ok',
            $this->_formatter->formatTimeStub($this->_record)
        );
    }

    /**
     * @covers Plop_Formatter::_formatTime
     */
    public function testFormatTime2()
    {
        $date = $this->getMock('DateTime');
        $date
            ->expects($this->once())
            ->method('format')
            ->with('U')
            ->will($this->returnValue('ok'));
        $this->_record
            ->expects($this->once())
            ->method('offsetGet')
            ->with('createdDate')
            ->will($this->returnValue($date));
        $this->assertSame(
            'ok',
            $this->_formatter->formatTimeStub($this->_record, 'U')
        );
    }

    /**
     * @covers Plop_Formatter::_formatException
     */
    public function testFormatException()
    {
        ini_set('display_errors', 0);
        $exc = new Exception('');
        $this->assertFalse($this->_formatter->formatExceptionStub($exc));
    }

    /**
     * @covers Plop_Formatter::_formatException
     */
    public function testFormatException2()
    {
        ini_set('display_errors', 1);
        $exc    = new Plop_Exception('Foo');
        $line   = __LINE__ - 1;
        $file   = __FILE__;
        $msg    = $this->_formatter->formatExceptionStub($exc);
        $msg    = substr($msg, 0, strcspn($msg, "\r\n"));
        $this->assertSame(
            "exception 'Plop_Exception' with message 'Foo' in $file:$line",
            $msg
        );
    }

    /**
     * @covers Plop_Formatter::_formatException
     */
    public function testFormatException3()
    {
        ini_set('display_errors', 1);
        $this->_formatter->setPythonLike(TRUE);
        $exc    = new Plop_Exception('Foo');
        $line   = __LINE__ - 1;
        $file   = __FILE__;
        $msg    = $this->_formatter->formatExceptionStub($exc);
        $msg1   = substr($msg, 0, strcspn($msg, "\r\n"));
        $msg2   = substr(strrchr($msg, "\n"), 1);
        $this->assertSame('Traceback (most recent call last):', $msg1);
        $this->assertSame(
            "Exception 'Plop_Exception' with message 'Foo' in $file:$line",
            $msg2
        );
    }

    /**
     * @covers Plop_Formatter::format
     */
    public function testFormatMethod()
    {
        $formatter = $this->getMock(
            'Plop_Formatter',
            array(
                '_formatException',
                '_formatTime',
            )
        );

        $formatter->setFormat('%(asctime)s - %(message)s');
        $exc = new Plop_Exception('Foo');

        $this->_record
            ->expects($this->once())
            ->method('getMessage')
            ->will($this->returnValue('Foo'));

        $this->_record
            ->expects($this->once())
            ->method('asArray')
            ->will($this->returnValue(array()));

        $this->_record->staticExpects($this->once())
            ->method('formatPercent')
            ->with('%(asctime)s - %(message)s', array())
            ->will($this->returnValue('Bar'));

        $formatter
            ->expects($this->once())
            ->method('_formatTime')
            ->with($this->_record, Plop_Formatter::DEFAULT_DATE_FORMAT)
            ->will($this->returnValue('Baz'));

        $formatter
            ->expects($this->once())
            ->method('_formatException')
            ->with($exc)
            ->will($this->returnValue('Qux'));

        $this->_setExpectations(
            $this->_record,
            'offsetSet',
            array(
                array('message', 'Foo'),
                array('asctime', 'Baz'),
                array('exc_text', 'Qux'),
            ),
            array(NULL, NULL, NULL)
        );

        $this->_setExpectations(
            $this->_record,
            'offsetGet',
            array(
                array('exc_info'),
                array('exc_text'),
                array('exc_info'),
                array('exc_text'),
                array('exc_text'),
            ),
            array($exc, FALSE, $exc, 'Plop_Exception', 'Plop_Exception')
        );

        $this->assertSame(
            "Bar\nPlop_Exception",
            $formatter->format($this->_record)
        );
    }
}
