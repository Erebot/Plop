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

class   Plop_Record_Test
extends Plop_TestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->_line    = __LINE__;
        $this->_record  = new Plop_Record(
            'loggerFile',
            'loggerClass',
            'loggerMethod',
            Plop::DEBUG,
            __FILE__,
            $this->_line,
            '@ %(foo)s @',
            array('foo' => 'bar')
        );
    }

    /**
     * @covers Plop_Record::__construct
     * @covers Plop_Record::asArray
     */
    public function testDefaultArguments()
    {
        $values = $this->_record->asArray();
        $this->assertSame('loggerFile',             $values['loggerFile']);
        $this->assertSame('loggerClass',            $values['loggerClass']);
        $this->assertSame('loggerMethod',           $values['loggerMethod']);
        $this->assertSame('@ %(foo)s @',            $values['msg']);
        $this->assertSame(array('foo' => 'bar'),    $values['args']);
        $this->assertSame('DEBUG',                  $values['levelname']);
        $this->assertSame(Plop::DEBUG,              $values['levelno']);
        $this->assertSame(__FILE__,                 $values['pathname']);
        $this->assertSame(__FILE__,                 $values['filename']);
        $this->assertSame('Unknown module',         $values['module']);
        $this->assertSame(NULL,                     $values['exc_info']);
        $this->assertSame(NULL,                     $values['exc_text']);
        $this->assertSame($this->_line,             $values['lineno']);
        $this->assertSame(NULL,                     $values['funcName']);
        $this->assertSame(NULL,                     $values['thread']);
        $this->assertSame(NULL,                     $values['threadName']);
        $this->assertSame(getmypid(),               $values['process']);
        $this->assertSame(php_uname('n'),           $values['hostname']);
    }

    /**
     * @covers Plop_Record::__construct
     * @covers Plop_Record::asArray
     */
    public function testDefaultArgumentsOverride()
    {
        $line   = __LINE__;
        $exc    = new Plop_Exception('');
        $script = $_SERVER['argv'][0];
        $_SERVER['argv'][0] = NULL;

        $record = new Plop_Record(
            'foo',
            'foo2',
            'foo3',
            Plop::ERROR,
            __FILE__,
            $line,
            'qux',
            array('bar' => 'baz'),
            $exc,
            __METHOD__
        );

        $values = $record->asArray();
        $this->assertSame('foo',                    $values['loggerFile']);
        $this->assertSame('foo2',                   $values['loggerClass']);
        $this->assertSame('foo3',                   $values['loggerMethod']);
        $this->assertSame('qux',                    $values['msg']);
        $this->assertSame(array('bar' => 'baz'),    $values['args']);
        $this->assertSame('ERROR',                  $values['levelname']);
        $this->assertSame(Plop::ERROR,              $values['levelno']);
        $this->assertSame(__FILE__,                 $values['pathname']);
        $this->assertSame(__FILE__,                 $values['filename']);
        $this->assertSame('Unknown module',         $values['module']);
        $this->assertSame($exc,                     $values['exc_info']);
        $this->assertSame(NULL,                     $values['exc_text']);
        $this->assertSame($line,                    $values['lineno']);
        $this->assertSame(__METHOD__,               $values['funcName']);
        $this->assertSame(NULL,                     $values['thread']);
        $this->assertSame(NULL,                     $values['threadName']);
        $this->assertSame(getmypid(),               $values['process']);
        $this->assertSame('-',                      $values['processName']);
        $this->assertSame(php_uname('n'),           $values['hostname']);
        $_SERVER['argv'][0] = $script;
    }

    /**
     * @covers Plop_Record::getMessage
     */
    public function testMessageGetter()
    {
        $this->assertSame('@ bar @', $this->_record->getMessage());
    }

    /**
     * @covers Plop_Record::formatPercent
     */
    public function testMessageFormatting()
    {
        $this->assertSame('foo', Plop_Record::formatPercent('foo'));
        $this->assertSame('foo', Plop_Record::formatPercent('foo', array()));
        $this->assertSame(
            'bar',
            Plop_Record::formatPercent('%(foo)s', array('foo' => 'bar'))
        );
        $this->assertSame(
            '007',
            Plop_Record::formatPercent('%(Bond)03d', array('Bond' => 7))
        );
        $this->assertSame(
            '3.14',
            Plop_Record::formatPercent('%(PI).2f', array('PI' => 3.14159265))
        );
        $this->assertSame(
            '%',
            Plop_Record::formatPercent('%(foo)s', array('foo' => '%'))
        );
        $this->assertSame(
            'foo',
            Plop_Record::formatPercent('%(%)s', array('%' => 'foo'))
        );
    }

    /**
     * @covers Plop_Record::offsetGet
     * @covers Plop_Record::offsetSet
     * @covers Plop_Record::offsetExists
     * @covers Plop_Record::offsetUnset
     */
    public function testArrayAccess()
    {
        $this->assertTrue(isset($this->_record['loggerFile']));
        $this->assertSame('loggerFile', $this->_record['loggerFile']);
        $this->_record['loggerFile'] = __FILE__;
        $this->assertSame(__FILE__, $this->_record['loggerFile']);
        unset($this->_record['loggerFile']);
        $this->assertFalse(isset($this->_record['loggerFile']));
    }

    /**
     * @covers Plop_Record::serialize
     * @covers Plop_Record::unserialize
     */
    public function testSerialization()
    {
        $file   = '...........................' .
                  '.........../tests/src/Plop/RecordTest.php';
        $data = 'C:11:"Plop_Record":755:{a:21:{s:4:"name";s:4:"name";' .
                's:3:"msg";s:11:"@ %(foo)s @";s:4:"args";a:1:{s:3:"foo";' .
                's:3:"bar";}s:9:"levelname";s:5:"DEBUG";s:7:"levelno";' .
                'i:10;s:8:"pathname";s:68:"...........................' .
                '.........../tests/src/Plop/RecordTest.php";s:8:"filename";' .
                's:68:"....................................../' .
                'tests/src/Plop/RecordTest.php";s:6:"module";' .
                's:14:"Unknown module";s:8:"exc_info";N;s:8:"exc_text";N;' .
                's:6:"lineno";i:33;s:8:"funcName";N;s:5:"msecs";' .
                'd:21691000000000;s:7:"created";d:1349290255;' .
                's:11:"createdDate";O:8:"DateTime":3:{s:4:"date";' .
                's:19:"2012-10-03 18:50:55";s:13:"timezone_type";i:3;' .
                's:8:"timezone";s:3:"UTC";}s:15:"relativeCreated";' .
                'd:84;s:6:"thread";N;s:10:"threadName";N;' .
                's:7:"process";i:3930;s:11:"processName";s:7:"phpunit";' .
                's:8:"hostname";s:6:"naraku";}}';
 
        $record = unserialize($data);
        $values = $record->asArray();
        $this->assertSame('name',                   $values['name']);
        $this->assertSame('@ %(foo)s @',            $values['msg']);
        $this->assertSame(array('foo' => 'bar'),    $values['args']);
        $this->assertSame('DEBUG',                  $values['levelname']);
        $this->assertSame(10,                       $values['levelno']);
        $this->assertSame($file,                    $values['pathname']);
        $this->assertSame($file,                    $values['filename']);
        $this->assertSame('Unknown module',         $values['module']);
        $this->assertSame(NULL,                     $values['exc_info']);
        $this->assertSame(NULL,                     $values['exc_text']);
        $this->assertSame(33,                       $values['lineno']);
        $this->assertSame(NULL,                     $values['funcName']);
        $this->assertSame(NULL,                     $values['thread']);
        $this->assertSame(NULL,                     $values['threadName']);
        $this->assertSame(3930,                     $values['process']);
        $this->assertSame('naraku',                 $values['hostname']);
        $this->assertSame($data, serialize($record));
    }
}
