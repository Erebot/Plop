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

class Record extends \Plop_TestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->line         = __LINE__;

        $this->interpolator = $this->getMock('\\Plop\\InterpolatorInterface');
        $this->interpolator
            ->expects($this->any())
            ->method('interpolate')
            ->will($this->returnArgument(0));

        $this->record       = new \Plop\Record(
            'loggerNamespace',
            'loggerClass',
            'loggerMethod',
            'namespace',
            'class',
            'method',
            \Plop\DEBUG,
            __FILE__,
            $this->line,
            '@ %(foo)s @',
            array('foo' => 'bar'),
            $this->interpolator
        );
    }

    /**
     * @covers \Plop\Record::__construct
     * @covers \Plop\Record::asArray
     */
    public function testDefaultArguments()
    {
        $expected   = array(
            'loggerNamespace'   => 'loggerNamespace',
            'loggerClass'       => 'loggerClass',
            'loggerMethod'      => 'loggerMethod',
            'namespace'         => 'namespace',
            'class'             => 'class',
            'method'            => 'method',
            'msg'               => '@ %(foo)s @',
            'args'              => array('foo' => 'bar'),
            'levelname'         => 'DEBUG',
            'levelno'           => \Plop\DEBUG,
            'pathname'          => __FILE__,
            'filename'          => __FILE__,
            'module'            => 'namespace',
            'exc_info'          => null,
            'exc_text'          => null,
            'lineno'            => $this->line,
//            'funcName'          => null,
            'threadId'          => null,
            'threadCreatorId'   => null,
            'process'           => getmypid(),
            'hostname'          => php_uname('n'),
        );
        $values = $this->record->asArray();
        foreach ($expected as $key => $val) {
            $this->assertSame($val, $values[$key]);
        }
    }

    /**
     * @covers \Plop\Record::__construct
     * @covers \Plop\Record::asArray
     */
    public function testDefaultArgumentsOverride()
    {
        $line   = __LINE__;
        $exc    = new \Plop\Exception('');
        $script = $_SERVER['argv'][0];
        $_SERVER['argv'][0] = null;

        $record = new \Plop\Record(
            'foo',
            'foo2',
            'foo3',
            'namespace',
            'class',
            'method',
            \Plop\ERROR,
            __FILE__,
            $line,
            'qux',
            array('bar' => 'baz'),
            $this->interpolator,
            $exc
        );

        $expected   = array(
            'loggerNamespace'   => 'foo',
            'loggerClass'       => 'foo2',
            'loggerMethod'      => 'foo3',
            'namespace'         => 'namespace',
            'class'             => 'class',
            'method'            => 'method',
            'msg'               => 'qux',
            'args'              => array('bar' => 'baz'),
            'levelname'         => 'ERROR',
            'levelno'           => \Plop\ERROR,
            'pathname'          => __FILE__,
            'filename'          => __FILE__,
            'module'            => 'namespace',
            'exc_info'          => $exc,
            'exc_text'          => null,
            'lineno'            => $line,
#            'funcName'          => __FUNCTION__, //! @FIXME
            'threadId'          => null,
            'threadCreatorId'   => null,
            'processName'       => '-',
            'hostname'          => php_uname('n'),
        );

        $values = $record->asArray();
        foreach ($expected as $key => $val) {
            $this->assertSame($val, $values[$key], "Differing values for key $key");
        }
        $_SERVER['argv'][0] = $script;
    }

    /**
     * @covers \Plop\Record::getInterpolator
     * @covers \Plop\Record::setInterpolator
     */
    public function testInterpolatorAccessors()
    {
        $interpolator = new \Plop\Interpolator\Percent();
        $this->assertNotSame($interpolator, $this->record->getInterpolator());
        // setInterpolator() returns $this.
        $this->assertSame($this->record, $this->record->setInterpolator($interpolator));
        $this->assertSame($interpolator, $this->record->getInterpolator());
    }

    /**
     * @covers \Plop\Record::getMessage
     */
    public function testMessageGetter()
    {
        $this->record->setInterpolator(new \Plop\Interpolator\Percent());
        $this->assertSame(
            '@ bar @',
            $this->record->getMessage()
        );
    }

    /**
     * @covers \Plop\Record::offsetGet
     * @covers \Plop\Record::offsetSet
     * @covers \Plop\Record::offsetExists
     * @covers \Plop\Record::offsetUnset
     */
    public function testArrayAccess()
    {
        $this->assertTrue(isset($this->record['loggerNamespace']));
        $this->assertSame('loggerNamespace', $this->record['loggerNamespace']);
        $this->record['loggerNamespace'] = __NAMESPACE__;
        $this->assertSame(__NAMESPACE__, $this->record['loggerNamespace']);
        unset($this->record['loggerNamespace']);
        $this->assertFalse(isset($this->record['loggerNamespace']));
    }

    /**
     * @covers \Plop\Record::serialize
     * @covers \Plop\Record::unserialize
     */
    public function testSerialization()
    {
        $record = new \Plop\Record(
            __NAMESPACE__,
            substr(__CLASS__, strrpos('\\' . __CLASS__, '\\')),
            substr(__FUNCTION__, strrpos('\\' . __FUNCTION__, '\\')),
            'namespace',
            'class',
            'method',
            \Plop\ERROR,
            __FILE__,
            __LINE__,
            'qux',
            array('bar' => 'baz'),
            new \Plop\Interpolator\Percent()
        );

        // Overwrite values that depend on time/platform.
        $record['msecs']            = 1337;
        $record['created']          = 42;
        $record['relativeCreated']  = 23;
        $record['createdDate']      = null;
        $record['process']          = 108;
        $record['processName']      = 'phpunit';
        $record['pathname']         = '/dev/null';
        $record['filename']         = '/dev/null';
        $record['hostname']         = 'conan';

        $data =<<<DATA
C:11:"Plop\\Record":743:{a:2:{i:0;a:26:{s:4:"args";a:1:{s:3:"bar";s:3:"baz";}
s:5:"class";s:5:"class";s:7:"created";i:42;s:11:"createdDate";N;
s:8:"exc_info";N;s:8:"exc_text";N;s:8:"filename";s:9:"/dev/null";
s:8:"funcName";s:6:"method";s:8:"hostname";s:5:"conan";s:9:"levelname";
s:5:"ERROR";s:7:"levelno";i:50;s:6:"lineno";i:201;s:11:"loggerClass";
s:6:"Record";s:12:"loggerMethod";s:17:"testSerialization";
s:15:"loggerNamespace";s:10:"Plop\Tests";s:6:"method";s:6:"method";
s:6:"module";s:9:"namespace";s:5:"msecs";i:1337;s:3:"msg";s:3:"qux";
s:9:"namespace";s:9:"namespace";s:8:"pathname";s:9:"/dev/null";
s:7:"process";i:108;s:11:"processName";s:7:"phpunit";
s:15:"relativeCreated";i:23;s:8:"threadId";N;s:15:"threadCreatorId";N;}i:1;
O:25:"Plop\\Interpolator\\Percent":0:{}}}
DATA;

        $data = str_replace(array("\r", "\n"), '', $data);
        $this->assertSame($data, serialize($record));
        $this->assertSame($record->asArray(), unserialize($data)->asArray());
    }
}
