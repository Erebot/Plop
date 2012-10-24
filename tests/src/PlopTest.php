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
    dirname(dirname(__FILE__)) .
    DIRECTORY_SEPARATOR . 'stubs' .
    DIRECTORY_SEPARATOR . 'Plop.php'
);

class   Plop_Test
extends Plop_TestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->_plop    = new Plop_Stub(TRUE);
        $this->_logger  = $this->getMock('Plop_LoggerInterface');
    }

    /**
     * @covers Plop::getInstance
     */
    public function testSingleton()
    {
        Plop_Stub::resetInstanceStub();
        $instanceA = Plop_Stub::getInstance();
        $instanceB = Plop_Stub::getInstance();
        $this->assertSame($instanceA, $instanceB);
    }

    /**
     * @covers Plop::__clone
     * @expectedException           Plop_Exception
     * @expectedExceptionMessage    Cloning this class is forbidden
     */
    public function testCloning()
    {
        clone $this->_plop;
    }

    /**
     * @covers Plop::getCreationDate
     */
    public function testCreationDateGetter()
    {
        $this->assertSame(12345678.9, $this->_plop->getCreationDate());
    }

    /**
     * @covers                      Plop::offsetSet
     * @expectedException           Plop_Exception
     * @expectedExceptionMessage    Invalid logger
     */
    public function testOffsetSetter()
    {
        $this->_plop[] = 'foo';
    }

    /**
     * @covers                      Plop::offsetSet
     * @expectedException           Plop_Exception
     * @expectedExceptionMessage    Identifier mismatch
     */
    public function testOffsetSetter2()
    {
        $this->_plop['foo'] = $this->_logger;
    }

    /**
     * @covers Plop::offsetSet
     */
    public function testOffsetSetter3()
    {
        $this->_plop['::'] = $this->_logger;
        $this->_plop[] = $this->_logger;
    }

    /**
     * @covers                      Plop::offsetGet
     * @expectedException           Plop_Exception
     * @expectedExceptionMessage    Invalid identifier
     */
    public function testOffsetGetter()
    {
        $this->_plop[42];
    }

    /**
     * @covers                      Plop::offsetGet
     * @expectedException           Plop_Exception
     * @expectedExceptionMessage    Invalid identifier
     */
    public function testOffsetGetter2()
    {
        $this->_plop[':'];
    }

    /**
     * @covers Plop::offsetGet
     * @covers Plop::getLogger
     */
    public function testOffsetGetter3()
    {
        $plop       = new Plop_Stub(FALSE);

        // The root logger must be returned
        // when no other logger matches.
        $logger = $plop['::' . __FILE__ . DIRECTORY_SEPARATOR];
        $this->assertSame(NULL, $logger->getFile());
        $this->assertSame(NULL, $logger->getClass());
        $this->assertSame(NULL, $logger->getMethod());

        // Add a logger for this file's directory (and test its retrieval).
        $dirLogger = $this->getMock('Plop_LoggerInterface');
        $dirLogger
            ->expects($this->any())
            ->method('getFile')
            ->will($this->returnValue(dirname(__FILE__)));
        $plop[] = $dirLogger;
        $logger = $plop->getLogger(__FILE__, __CLASS__, __FUNCTION__);
        $this->assertSame($dirLogger, $logger);

        // Add a logger for this class (and test its retrieval).
        $classLogger = $this->getMock('Plop_LoggerInterface');
        $classLogger
            ->expects($this->any())
            ->method('getFile')
            ->will($this->returnValue(__FILE__));
        $classLogger
            ->expects($this->any())
            ->method('getClass')
            ->will($this->returnValue(__CLASS__));
        $plop[] = $classLogger;
        $logger = $plop->getLogger(__FILE__, __CLASS__, __FUNCTION__);
        $this->assertSame($classLogger, $logger);

        // Add a logger for this method (and test its retrieval).
        $methodLogger = $this->getMock('Plop_LoggerInterface');
        $methodLogger
            ->expects($this->any())
            ->method('getFile')
            ->will($this->returnValue(__FILE__));
        $methodLogger
            ->expects($this->any())
            ->method('getClass')
            ->will($this->returnValue(__CLASS__));
        $methodLogger
            ->expects($this->any())
            ->method('getMethod')
            ->will($this->returnValue(__FUNCTION__));
        $plop[] = $methodLogger;
        $logger = $plop->getLogger(__FILE__, __CLASS__, __FUNCTION__);
        $this->assertSame($methodLogger, $logger);
    }

    /**
     * @covers                      Plop::offsetExists
     * @expectedException           Plop_Exception
     * @expectedExceptionMessage    Invalid identifier
     */
    public function testOffsetExistence()
    {
        isset($this->_plop[42]);
    }

    /**
     * @covers Plop::offsetExists
     */
    public function testOffsetExistence2()
    {
        $plop = new Plop_Stub(FALSE);
        $this->assertTrue(isset($plop[$this->_logger]));
        $this->assertFalse(isset($plop['foo']));
    }


    /**
     * @covers                      Plop::offsetUnset
     * @expectedException           Plop_Exception
     * @expectedExceptionMessage    The root logger cannot be unset
     */
    public function testOffsetUnsetter()
    {
        unset($this->_plop[$this->_logger]);
    }

    /**
     * @covers Plop::offsetUnset
     */
    public function testOffsetUnsetter2()
    {
        $this->_logger
            ->expects($this->once())
            ->method('getFile')
            ->will($this->returnValue('foo'));
        unset($this->_plop[$this->_logger]);
    }

    /**
     * @covers Plop::count
     */
    public function testCountMethod()
    {
        $this->_logger
            ->expects($this->once())
            ->method('getFile')
            ->will($this->returnValue(__FILE__));

        $this->assertSame(0, count($this->_plop));
        $this->_plop[] = $this->_logger;
        $this->assertSame(1, count($this->_plop));
    }

    /**
     * @covers                      Plop::addLevelName
     * @expectedException           Plop_Exception
     * @expectedExceptionMessage    Invalid level value
     */
    public function testAddLevelNameMethod()
    {
        $this->_plop->addLevelName('foo', 'bar');
    }

    /**
     * @covers                      Plop::addLevelName
     * @expectedException           Plop_Exception
     * @expectedExceptionMessage    Invalid level name
     */
    public function testAddLevelNameMethod2()
    {
        $this->_plop->addLevelName(42, 69);
    }

    /**
     * @covers                      Plop::getLevelName
     * @expectedException           Plop_Exception
     * @expectedExceptionMessage    Invalid level value
     */
    public function testGetLevelNameMethod()
    {
        $this->_plop->getLevelName('foo');
    }

    /**
     * @covers                      Plop::getLevelValue
     * @expectedException           Plop_Exception
     * @expectedExceptionMessage    Invalid level name
     */
    public function testGetLevelValueMethod()
    {
        $this->_plop->getLevelValue(42);
    }

    /**
     * @covers Plop::addLevelName
     * @covers Plop::getLevelName
     * @covers Plop::getLevelValue
     */
    public function testLevelNameMapping()
    {
        // "Level N" is returned for unknown level value N.
        $this->assertSame('Level 42', $this->_plop->getLevelName(42));
        // Plop::NOTSET is returned for an unknown level name.
        $this->assertSame(Plop::NOTSET, $this->_plop->getLevelValue('foo'));

        // Now, register the new name and test the methods again.
        $this->assertSame($this->_plop, $this->_plop->addLevelName('foo', 42));
        $this->assertSame('foo', $this->_plop->getLevelName(42));
        $this->assertSame(42, $this->_plop->getLevelValue('foo'));
    }

    /**
     * @covers Plop::addLogger
     */
    public function testAddLoggerMethod()
    {
        $plop = $this->getMock('Plop_Stub', array('offsetSet'), array(TRUE));
        $plop
            ->expects($this->once())
            ->method('offsetSet')
            ->with(NULL, $this->_logger);
        $this->assertSame($plop, $plop->addLogger($this->_logger));
    }

    /**
     * @covers Plop::_getLoggerId
     */
    public function testGetLoggerIdMethod()
    {
        $this->_logger
            ->expects($this->once())
            ->method('getFile')
            ->will($this->returnValue(__FILE__));
        $this->_logger
            ->expects($this->once())
            ->method('getClass')
            ->will($this->returnValue(__CLASS__));
        $this->_logger
            ->expects($this->once())
            ->method('getMethod')
            ->will($this->returnValue(__FUNCTION__));

        $id =  __FUNCTION__ . ':' . __CLASS__ . ':' . __FILE__;
        $this->assertSame($id, Plop_Stub::getLoggerIdStub($this->_logger));
    }

    /**
     * @covers Plop::__construct
     */
    public function testConstructor()
    {
        $plop = Plop::getInstance();
        $this->assertSame(1, count($plop));
        $logger = $plop['::'];
        $this->assertNotSame(NULL, $logger);
        $this->assertTrue($logger instanceof Plop_Logger);
        $handlers = $logger->getHandlers();
        $this->assertSame(1, count($handlers));
        $this->assertTrue($handlers[0] instanceof Plop_Handler_Stream);
        $this->assertTrue(
            $handlers[0]->getFormatter() instanceof Plop_Formatter
        );
        $this->assertSame(Plop::NOTSET,     $plop->getLevelValue('NOTSET'));
        $this->assertSame(Plop::DEBUG,      $plop->getLevelValue('DEBUG'));
        $this->assertSame(Plop::INFO,       $plop->getLevelValue('INFO'));
        $this->assertSame(Plop::WARNING,    $plop->getLevelValue('WARNING'));
        $this->assertSame(Plop::ERROR,      $plop->getLevelValue('ERROR'));
        $this->assertSame(Plop::CRITICAL,   $plop->getLevelValue('CRITICAL'));
    }

    /**
     * @covers Plop::_getIndirectLogger
     */
    public function testIndirectLogging()
    {
        $this->_logger
            ->expects($this->once())
            ->method('getFile')
            ->will($this->returnValue(dirname(dirname(__FILE__))));
        $this->_logger
            ->expects($this->once())
            ->method('getClass')
            ->will($this->returnValue(NULL));
        $this->_logger
            ->expects($this->once())
            ->method('getMethod')
            ->will($this->returnValue(NULL));

        $this->_plop[] = $this->_logger;
        $this->assertSame(
            $this->_logger,
            $this->_plop->getIndirectLoggerStub()
        );
    }

    /**
     * @covers Plop::findCaller
     */
    public function testFindCallerMethod()
    {
        $line   = __LINE__ + 1;
        $caller = Plop::findCaller();
        $this->assertSame(__FILE__,     $caller['fn']);
        $this->assertSame(__CLASS__,    $caller['class']);
        $this->assertSame(__FUNCTION__, $caller['func']);
        $this->assertSame($line,        $caller['lno']);
    }
}
