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

namespace Plop\Tests;

class Plop extends \Plop_TestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->plop     = new \Plop\Stub\Plop(true);
        $this->logger   = $this->getMockBuilder('\\Plop\\LoggerInterface')->getMock();
    }

    /**
     * @covers \Plop\Plop::getInstance
     */
    public function testSingleton()
    {
        \Plop\Stub\Plop::resetInstanceStub();
        $instanceA = \Plop\Stub\Plop::getInstance();
        $instanceB = \Plop\Stub\Plop::getInstance();
        $this->assertSame($instanceA, $instanceB);
    }

    /**
     * @covers \Plop\Plop::__clone
     * @expectedException           \Plop\Exception
     * @expectedExceptionMessage    Cloning this class is forbidden
     */
    public function testCloning()
    {
        clone $this->plop;
    }

    /**
     * @covers \Plop\Plop::getCreationDate
     */
    public function testCreationDateGetter()
    {
        $this->assertSame(12345678.9, $this->plop->getCreationDate());
    }

    /**
     * @covers                      \Plop\Plop::offsetSet
     * @expectedException           \Plop\Exception
     * @expectedExceptionMessage    Invalid logger
     */
    public function testOffsetSetter()
    {
        $this->plop[] = 'foo';
    }

    /**
     * @covers                      \Plop\Plop::offsetSet
     * @expectedException           \Plop\Exception
     * @expectedExceptionMessage    Identifier mismatch
     */
    public function testOffsetSetter2()
    {
        $this->plop['foo'] = $this->logger;
    }

    /**
     * @covers \Plop\Plop::offsetSet
     */
    public function testOffsetSetter3()
    {
        $this->plop['::'] = $this->logger;
        $this->assertSame($this->logger, $this->plop['::']);
        $this->plop[] = $this->logger;
        $this->assertSame($this->logger, $this->plop['::']);
    }

    /**
     * @covers                      \Plop\Plop::offsetGet
     * @expectedException           \Plop\Exception
     * @expectedExceptionMessage    Invalid identifier
     */
    public function testOffsetGetter()
    {
        $this->plop[42];
    }

    /**
     * @covers                      \Plop\Plop::offsetGet
     * @expectedException           \Plop\Exception
     * @expectedExceptionMessage    Invalid identifier
     */
    public function testOffsetGetter2()
    {
        $this->plop[':'];
    }

    /**
     * @covers \Plop\Plop::offsetGet
     * @covers \Plop\Plop::getLogger
     */
    public function testOffsetGetter3()
    {
        $plop       = new \Plop\Stub\Plop(false);

        // The root logger must be returned
        // when no other logger matches.
        $logger = $plop['::' . __NAMESPACE__];
        $this->assertSame(null, $logger->getNamespace());
        $this->assertSame(null, $logger->getClass());
        $this->assertSame(null, $logger->getMethod());

        // Add a logger for this file's directory (and test its retrieval).
        $dirLogger = $this->getMockBuilder('\\Plop\\LoggerInterface')->getMock();
        $dirLogger
            ->expects($this->any())
            ->method('getNamespace')
            ->will($this->returnValue(__NAMESPACE__));
        $plop[] = $dirLogger;
        $logger = $plop->getLogger(__NAMESPACE__, __CLASS__, __FUNCTION__);
        $this->assertSame($dirLogger, $logger);

        // Add a logger for this class (and test its retrieval).
        $classLogger = $this->getMockBuilder('\\Plop\\LoggerInterface')->getMock();
        $classLogger
            ->expects($this->any())
            ->method('getNamespace')
            ->will($this->returnValue(__NAMESPACE__));
        $classLogger
            ->expects($this->any())
            ->method('getClass')
            ->will(
                $this->returnValue(
                    substr(__CLASS__, strrpos('\\' . __CLASS__, '\\'))
                )
            );
        $plop[] = $classLogger;
        $logger = $plop->getLogger(__NAMESPACE__, __CLASS__, __FUNCTION__);
        $this->assertSame($classLogger, $logger);

        // Add a logger for this method (and test its retrieval).
        $methodLogger = $this->getMockBuilder('\\Plop\\LoggerInterface')->getMock();
        $methodLogger
            ->expects($this->any())
            ->method('getNamespace')
            ->will($this->returnValue(__NAMESPACE__));
        $methodLogger
            ->expects($this->any())
            ->method('getClass')
            ->will(
                $this->returnValue(
                    substr(__CLASS__, strrpos('\\' . __CLASS__, '\\'))
                )
            );
        $methodLogger
            ->expects($this->any())
            ->method('getMethod')
            ->will($this->returnValue(__FUNCTION__));
        $plop[] = $methodLogger;
        $logger = $plop->getLogger(__NAMESPACE__, __CLASS__, __FUNCTION__);
        $this->assertSame($methodLogger, $logger);
    }

    /**
     * @covers                      \Plop\Plop::offsetExists
     * @expectedException           \Plop\Exception
     * @expectedExceptionMessage    Invalid identifier
     */
    public function testOffsetExistence()
    {
        isset($this->plop[42]);
    }

    /**
     * @covers \Plop\Plop::offsetExists
     */
    public function testOffsetExistence2()
    {
        $plop = new \Plop\Stub\Plop(false);
        $this->assertTrue(isset($plop[$this->logger]));
        $this->assertFalse(isset($plop['foo']));
    }


    /**
     * @covers                      \Plop\Plop::offsetUnset
     * @expectedException           \Plop\Exception
     * @expectedExceptionMessage    The root logger cannot be unset
     */
    public function testOffsetUnsetter()
    {
        unset($this->plop[$this->logger]);
    }

    /**
     * @covers \Plop\Plop::offsetUnset
     */
    public function testOffsetUnsetter2()
    {
        $this->logger
            ->expects($this->once())
            ->method('getNamespace')
            ->will($this->returnValue('foo'));
        unset($this->plop[$this->logger]);
    }

    /**
     * @covers \Plop\Plop::count
     */
    public function testCountMethod()
    {
        $this->logger
            ->expects($this->exactly(1))
            ->method('getNamespace')
            ->will($this->returnValue(__NAMESPACE__));
        $this->logger
            ->expects($this->exactly(1))
            ->method('getClass')
            ->will($this->returnValue(__CLASS__));
        $this->logger
            ->expects($this->exactly(1))
            ->method('getMethod')
            ->will($this->returnValue(__FUNCTION__));

        $this->assertSame(0, count($this->plop));
        $this->plop[] = $this->logger;
        $this->assertSame(1, count($this->plop));
    }

    /**
     * @covers                      \Plop\Plop::addLevelName
     * @expectedException           \Plop\Exception
     * @expectedExceptionMessage    Invalid level value
     */
    public function testAddLevelNameMethod()
    {
        $this->plop->addLevelName('foo', 'bar');
    }

    /**
     * @covers                      \Plop\Plop::addLevelName
     * @expectedException           \Plop\Exception
     * @expectedExceptionMessage    Invalid level name
     */
    public function testAddLevelNameMethod2()
    {
        $this->plop->addLevelName(42, 69);
    }

    /**
     * @covers                      \Plop\Plop::getLevelName
     * @expectedException           \Plop\Exception
     * @expectedExceptionMessage    Invalid level value
     */
    public function testGetLevelNameMethod()
    {
        $this->plop->getLevelName('foo');
    }

    /**
     * @covers                      \Plop\Plop::getLevelValue
     * @expectedException           \Plop\Exception
     * @expectedExceptionMessage    Invalid level name
     */
    public function testGetLevelValueMethod()
    {
        $this->plop->getLevelValue(42);
    }

    /**
     * @covers \Plop\Plop::addLevelName
     * @covers \Plop\Plop::getLevelName
     * @covers \Plop\Plop::getLevelValue
     */
    public function testLevelNameMapping()
    {
        // "Level N" is returned for unknown level value N.
        $this->assertSame('Level 42', $this->plop->getLevelName(42));
        // \Plop\NOTSET is returned for an unknown level name.
        $this->assertSame(\Plop\NOTSET, $this->plop->getLevelValue('foo'));

        // Now, register the new name and test the methods again.
        $this->assertSame($this->plop, $this->plop->addLevelName('foo', 42));
        $this->assertSame('foo', $this->plop->getLevelName(42));
        $this->assertSame(42, $this->plop->getLevelValue('foo'));
    }

    /**
     * @covers \Plop\Plop::addLogger
     */
    public function testAddLoggerMethod()
    {
        $plop = $this->getMockBuilder('\\Plop\\Stub\\Plop')
            ->setMethods(array('offsetSet'))
            ->setConstructorArgs(array(true))
            ->getMock();
        $plop
            ->expects($this->once())
            ->method('offsetSet')
            ->with(null, $this->logger);
        $this->assertSame($plop, $plop->addLogger($this->logger));
    }

    /**
     * @covers \Plop\Plop::getLoggerId
     */
    public function testGetLoggerIdMethod()
    {
        $this->logger
            ->expects($this->once())
            ->method('getNamespace')
            ->will($this->returnValue(__NAMESPACE__));
        $this->logger
            ->expects($this->once())
            ->method('getClass')
            ->will($this->returnValue(__CLASS__));
        $this->logger
            ->expects($this->once())
            ->method('getMethod')
            ->will($this->returnValue(__FUNCTION__));

        $id =  __FUNCTION__ . ':' . __CLASS__ . ':' . __NAMESPACE__;
        $this->assertSame($id, \Plop\Stub\Plop::getLoggerIdStub($this->logger));
    }

    /**
     * @covers \Plop\Plop::__construct
     */
    public function testConstructor()
    {
        $plop = \Plop\Stub\Plop::getInstance();
        $this->assertSame(1, count($plop));
        $logger = $plop['::'];
        $this->assertNotSame(null, $logger);
        $this->assertTrue($logger instanceof \Plop\Logger);
        $handlers = $logger->getHandlers();
        $this->assertSame(1, count($handlers));
        $this->assertTrue($handlers[0] instanceof \Plop\Handler\Stream);
        $this->assertSame(\Plop\NOTSET, $plop->getLevelValue('NOTSET'));
        $this->assertSame(\Plop\DEBUG, $plop->getLevelValue('DEBUG'));
        $this->assertSame(\Plop\INFO, $plop->getLevelValue('INFO'));
        $this->assertSame(\Plop\WARNING, $plop->getLevelValue('WARNING'));
        $this->assertSame(\Plop\ERROR, $plop->getLevelValue('ERROR'));
        $this->assertSame(\Plop\CRITICAL, $plop->getLevelValue('CRITICAL'));
    }

    /**
     * @covers \Plop\Plop::getIndirectLogger
     */
    public function testIndirectLogging()
    {
        $this->logger
            ->expects($this->exactly(1))
            ->method('getNamespace')
            ->will($this->returnValue(null));
        $this->logger
            ->expects($this->exactly(1))
            ->method('getClass')
            ->will($this->returnValue(null));
        $this->logger
            ->expects($this->exactly(1))
            ->method('getMethod')
            ->will($this->returnValue(null));

        $this->plop[] = $this->logger;
        $this->assertSame(
            $this->logger,
            $this->plop->getIndirectLoggerStub()
        );
    }

    /**
     * @covers \Plop\Plop::findCaller
     */
    public function testFindCallerMethod()
    {
        $line   = __LINE__ + 1;
        $caller = \Plop\Plop::findCaller();
        // Strip the namespace from the class name.
        $cls    = substr('\\' . __CLASS__, strrpos('\\' . __CLASS__, '\\') + 1);

        $this->assertSame(__NAMESPACE__, $caller['ns']);
        $this->assertSame(__FILE__, $caller['file']);
        $this->assertSame($line, $caller['line']);
        $this->assertSame(__FUNCTION__, $caller['func']);
        $this->assertSame($cls, $caller['cls']);
    }
}
