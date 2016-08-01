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

class Logger extends \Plop_TestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->logger      = new \Plop\Logger();
        $this->factory     = $this->getMockBuilder('\\Plop\\RecordFactoryInterface')->getMock();
        $this->record      = $this->getMockBuilder('\\Plop\\Stub\\RecordInterface')->getMock();
        $this->handler     = $this->getMockBuilder('\\Plop\\HandlerInterface')->getMock();
    }

    /**
     * @covers \Plop\Logger::__construct
     * @covers \Plop\Logger::getNamespace
     * @covers \Plop\Logger::getClass
     * @covers \Plop\Logger::getMethod
     */
    public function testConstructorWithDefaultArguments()
    {
        $this->assertSame(null, $this->logger->getNamespace());
        $this->assertSame(null, $this->logger->getClass());
        $this->assertSame(null, $this->logger->getMethod());
        $this->assertSame(\Plop\NOTSET, $this->logger->getLevel());
        $this->assertSame(0, count($this->logger->getHandlers()));
        $this->assertSame(0, count($this->logger->getFilters()));
    }

    /**
     * @covers \Plop\Logger::__construct
     * @covers \Plop\Logger::getNamespace
     * @covers \Plop\Logger::getClass
     * @covers \Plop\Logger::getMethod
     */
    public function testConstructorWithSpecificArguments()
    {
        $logger = new \Plop\Logger(
            __NAMESPACE__,
            null,
            null
        );

        $this->assertSame(__NAMESPACE__, $logger->getNamespace());
        $this->assertSame(null, $logger->getClass());
        $this->assertSame(null, $logger->getMethod());
        $this->assertSame(\Plop\NOTSET, $this->logger->getLevel());
        $this->assertSame(0, count($this->logger->getHandlers()));
        $this->assertSame(0, count($this->logger->getFilters()));
    }

    /**
     * @covers \Plop\Logger::__construct
     * @covers \Plop\Logger::getNamespace
     * @covers \Plop\Logger::getClass
     * @covers \Plop\Logger::getMethod
     */
    public function testConstructorWithSpecificArguments2()
    {
        $logger = new \Plop\Logger(
            null,
            __CLASS__,
            __FUNCTION__
        );

        $this->assertSame(null, $logger->getNamespace());
        $this->assertSame(
            substr(__CLASS__, strrpos('\\' . __CLASS__, '\\')),
            $logger->getClass()
        );
        $this->assertSame(__FUNCTION__, $logger->getMethod());
        $this->assertSame(\Plop\NOTSET, $this->logger->getLevel());
        $this->assertSame(0, count($this->logger->getHandlers()));
        $this->assertSame(0, count($this->logger->getFilters()));
    }

    /**
     * @covers \Plop\Logger::getRecordFactory
     * @covers \Plop\Logger::setRecordFactory
     */
    public function testRecordFactoryAccessors()
    {
        $factory = $this->logger->getRecordFactory();
        $this->assertTrue($factory instanceof \Plop\RecordFactory);
        $this->assertSame(
            $this->logger,
            $this->logger->setRecordFactory($this->factory)
        );
        $this->assertSame($this->factory, $this->logger->getRecordFactory());
    }

    /**
     * @covers \Plop\Logger::getLevel
     * @covers \Plop\Logger::setLevel
     * @covers \Plop\Logger::isEnabledFor
     */
    public function testLevelAccessors()
    {
        $this->assertNotEquals(\Plop\ERROR, $this->logger->getLevel());
        $this->assertSame(
            $this->logger,
            $this->logger->setLevel(\Plop\ERROR)
        );
        $this->assertSame(\Plop\ERROR, $this->logger->getLevel());

        $this->assertTrue($this->logger->isEnabledFor(\Plop\ERROR));
        $this->assertFalse($this->logger->isEnabledFor(\Plop\WARN));
        $this->assertFalse($this->logger->isEnabledFor(\Plop\WARNING));
        $this->assertFalse($this->logger->isEnabledFor(\Plop\INFO));
        $this->assertFalse($this->logger->isEnabledFor(\Plop\DEBUG));

        // Same thing, using strings instead of (constant) integers.
        $this->logger->setLevel('ERROR');
        $this->assertSame(\Plop\ERROR, $this->logger->getLevel());

        $this->assertTrue($this->logger->isEnabledFor('ERROR'));
        $this->assertFalse($this->logger->isEnabledFor('WARN'));
        $this->assertFalse($this->logger->isEnabledFor('WARNING'));
        $this->assertFalse($this->logger->isEnabledFor('INFO'));
        $this->assertFalse($this->logger->isEnabledFor('DEBUG'));
    }

    /**
     * @covers \Plop\Logger::handle
     */
    public function testHandleMethod()
    {
        $filters     = $this->getMockBuilder('\\Plop\\FiltersCollectionAbstract')->getMock();
        $logger = $this->getMockBuilder('\\Plop\\Stub\\Logger')
            ->setMethods(array('callHandlers'))
            ->setConstructorArgs(array(null, null, null, null, $filters))
            ->getMock();

        $filters
            ->expects($this->once())
            ->method('filter')
            ->with($this->record)
            ->will($this->returnValue(true));
        $logger
            ->expects($this->once())
            ->method('callHandlers')
            ->with($this->record);

        $this->assertSame($logger, $logger->handleStub($this->record));
    }

    /**
     * @covers \Plop\Logger::handle
     */
    public function testHandleMethod2()
    {
        $filters     = $this->getMockBuilder('\\Plop\\FiltersCollectionAbstract')->getMock();
        $logger = $this->getMockBuilder('\\Plop\\Stub\\Logger')
            ->setMethods(array('callHandlers'))
            ->setConstructorArgs(array(null, null, null, null, $filters))
            ->getMock();

        $filters
            ->expects($this->once())
            ->method('filter')
            ->with($this->record)
            ->will($this->returnValue(false));
        $logger
            ->expects($this->never())
            ->method('callHandlers');

        $this->assertSame($logger, $logger->handleStub($this->record));
    }

    /**
     * @covers \Plop\Logger::log
     */
    public function testLogMethod()
    {
        $logger = $this->getMockBuilder('\\Plop\\Stub\\Logger')
            ->setMethods(array('handle'))
            ->getMock();

        $this->factory
            ->expects($this->once())
            ->method('createRecord')
            ->will($this->returnValue($this->record));
        $logger
            ->expects($this->once())
            ->method('handle')
            ->with($this->record);

        $logger->setLevel(\Plop\DEBUG);
        $logger->setRecordFactory($this->factory);
        $this->assertSame($logger, $logger->log(\Plop\ERROR, 'foo'));
    }

    /**
     * @covers \Plop\Logger::log
     */
    public function testLogMethod2()
    {
        $logger = $this->getMockBuilder('\\Plop\\Stub\\Logger')
            ->setMethods(array('handle'))
            ->getMock();

        $this->factory
            ->expects($this->never())
            ->method('createRecord');
        $logger
            ->expects($this->never())
            ->method('handle');

        $logger->setLevel(\Plop\ERROR);
        $logger->setRecordFactory($this->factory);
        $this->assertSame($logger, $logger->log(\Plop\DEBUG, 'foo'));
    }

    /**
     * @covers \Plop\Logger::getFilters
     * @covers \Plop\Logger::setFilters
     */
    public function testFiltersAccessors()
    {
        $collection = new \Plop\FiltersCollection();
        $this->assertNotSame($collection, $this->logger->getFilters());
        // setFilters() returns $this.
        $this->assertSame($this->logger, $this->logger->setFilters($collection));
        $this->assertSame($collection, $this->logger->getFilters());
    }

    /**
     * @covers \Plop\Logger::getHandlers
     * @covers \Plop\Logger::setHandlers
     */
    public function testHandlersAccessors()
    {
        $collection = new \Plop\HandlersCollection();
        $this->assertNotSame($collection, $this->logger->getHandlers());
        // setHandlers() returns $this.
        $this->assertSame($this->logger, $this->logger->setHandlers($collection));
        $this->assertSame($collection, $this->logger->getHandlers());
    }
}
