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
    DIRECTORY_SEPARATOR . 'Logger.php'
);

require_once(
    dirname(dirname(dirname(__FILE__))) .
    DIRECTORY_SEPARATOR . 'stubs' .
    DIRECTORY_SEPARATOR . 'RecordInterface.php'
);

class   Plop_Logger_Test
extends Plop_TestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->_logger = new Plop_Logger();
        $this->_factory = $this->getMock('Plop_RecordFactoryInterface');
        $this->_record  = $this->getMock('Plop_RecordInterface_Stub');
        $this->_handler = $this->getMock('Plop_HandlerInterface');
    }

    /**
     * @covers Plop_Logger::__construct
     * @covers Plop_Logger::getFile
     * @covers Plop_Logger::getClass
     * @covers Plop_Logger::getMethod
     */
    public function testConstructorWithDefaultArguments()
    {
        $this->assertSame(NULL, $this->_logger->getFile());
        $this->assertSame(NULL, $this->_logger->getClass());
        $this->assertSame(NULL, $this->_logger->getMethod());
        $this->assertSame(Plop::NOTSET, $this->_logger->getLevel());
        $this->assertSame(array(), $this->_logger->getHandlers());
    }

    /**
     * @covers Plop_Logger::__construct
     * @covers Plop_Logger::getFile
     * @covers Plop_Logger::getClass
     * @covers Plop_Logger::getMethod
     */
    public function testConstructorWithSpecificArguments()
    {
        $logger = new Plop_Logger(
            __FILE__ . DIRECTORY_SEPARATOR,
            __CLASS__,
            __METHOD__
        );

        $this->assertSame(__FILE__, $logger->getFile());
        $this->assertSame(__CLASS__, $logger->getClass());
        $this->assertSame(__METHOD__, $logger->getMethod());
        $this->assertSame(Plop::NOTSET, $this->_logger->getLevel());
        $this->assertSame(array(), $this->_logger->getHandlers());
    }

    /**
     * @covers Plop_Logger::getRecordFactory
     * @covers Plop_Logger::setRecordFactory
     */
    public function testRecordFactoryAccessors()
    {
        $factory = $this->_logger->getRecordFactory();
        $this->assertTrue($factory instanceof Plop_RecordFactory);
        $this->assertSame(
            $this->_logger,
            $this->_logger->setRecordFactory($this->_factory)
        );
        $this->assertSame($this->_factory, $this->_logger->getRecordFactory());
    }

    /**
     * @covers Plop_Logger::getHandlers
     * @covers Plop_Logger::addHandler
     * @covers Plop_Logger::removeHandler
     */
    public function testHandlers()
    {
        $this->assertSame(array(), $this->_logger->getHandlers());
        $this->assertSame(
            $this->_logger,
            $this->_logger->addHandler($this->_handler)
        );
        $this->assertSame(
            $this->_logger,
            $this->_logger->addHandler($this->_handler)
        );
        $this->assertSame(
            array($this->_handler),
            $this->_logger->getHandlers()
        );

        $this->assertSame(
            $this->_logger,
            $this->_logger->removeHandler($this->_handler)
        );
        $this->assertSame(
            $this->_logger,
            $this->_logger->removeHandler($this->_handler)
        );
        $this->assertSame(array(), $this->_logger->getHandlers());
    }

    /**
     * @covers Plop_Logger::getLevel
     * @covers Plop_Logger::setLevel
     * @covers Plop_Logger::isEnabledFor
     */
    public function testLevelAccessors()
    {
        $this->assertNotEquals(Plop::ERROR, $this->_logger->getLevel());
        $this->assertSame(
            $this->_logger,
            $this->_logger->setLevel(Plop::ERROR)
        );
        $this->assertSame(Plop::ERROR, $this->_logger->getLevel());

        $this->assertTrue( $this->_logger->isEnabledFor(Plop::ERROR));
        $this->assertFalse($this->_logger->isEnabledFor(Plop::WARN));
        $this->assertFalse($this->_logger->isEnabledFor(Plop::WARNING));
        $this->assertFalse($this->_logger->isEnabledFor(Plop::INFO));
        $this->assertFalse($this->_logger->isEnabledFor(Plop::DEBUG));
    }

    /**
     * @covers Plop_Logger::setLevel
     * @expectedException           Plop_Exception
     * @expectedExceptionMessage    Invalid value
     */
    public function testLevelAccessors2()
    {
        $this->_logger->setLevel('foo');
    }

    /**
     * @covers Plop_Logger::isEnabledFor
     * @expectedException           Plop_Exception
     * @expectedExceptionMessage    Invalid value
     */
    public function testLevelAccessors3()
    {
        $this->_logger->isEnabledFor('foo');
    }

    /**
     * @covers Plop_Logger::_callHandlers
     */
    public function testHandlersCalling()
    {
        // If no handler has been registered, emit a warning.
        $logger = $this->getMock('Plop_Logger_Stub', array('_getStderr'));
        $logger
            ->expects($this->once())
            ->method('_getStderr')
            ->will($this->returnValue($this->stderrStream));

        // The warning may only be emitted once.
        // So we call the method twice and check the final output.
        $logger->callHandlersStub($this->_record);
        $logger->callHandlersStub($this->_record);
        $this->expectStderrString(
            'No handlers could be found for logger ("" in "")' . "\n"
        );
    }

    /**
     * @covers Plop_Logger::_callHandlers
     */
    public function testHandlersCalling2()
    {
        // If the log's level is greater or equal tp an handler's level,
        // the handler is called and not warning is emitted.
        $logger = $this->getMock('Plop_Logger_Stub', array('_getStderr'));
        $logger
            ->expects($this->never())
            ->method('_getStderr')
            ->will($this->returnValue($this->stderrStream));
        $this->_handler
            ->expects($this->once())
            ->method('getLevel')
            ->will($this->returnValue(10));
        $this->_record
            ->expects($this->once())
            ->method('offsetGet')
            ->with('levelno')
            ->will($this->returnValue(20));
        $this->_handler
            ->expects($this->once())
            ->method('handle')
            ->with($this->_record);

        $logger->addHandler($this->_handler);
        $logger->callHandlersStub($this->_record);
    }

    /**
     * @covers Plop_Logger::_callHandlers
     */
    public function testHandlersCalling3()
    {
        // The handlers never gets called if the log's level
        // is less than its own level.
        $logger = $this->getMock('Plop_Logger_Stub', array('_getStderr'));
        $logger
            ->expects($this->never())
            ->method('_getStderr')
            ->will($this->returnValue($this->stderrStream));
        $this->_handler
            ->expects($this->once())
            ->method('getLevel')
            ->will($this->returnValue(20));
        $this->_record
            ->expects($this->once())
            ->method('offsetGet')
            ->with('levelno')
            ->will($this->returnValue(10));
        $this->_handler
            ->expects($this->never())
            ->method('handle');

        $logger->addHandler($this->_handler);
        $logger->callHandlersStub($this->_record);
    }

    /**
     * @covers Plop_Logger::_handle
     */
    public function testHandleMethod()
    {
        $logger = $this->getMock(
            'Plop_Logger_Stub',
            array('filter', '_callHandlers')
        );

        $logger
            ->expects($this->once())
            ->method('filter')
            ->with($this->_record)
            ->will($this->returnValue(TRUE));
        $logger
            ->expects($this->once())
            ->method('_callHandlers')
            ->with($this->_record);

        $this->assertSame($logger, $logger->handleStub($this->_record));
    }

    /**
     * @covers Plop_Logger::_handle
     */
    public function testHandleMethod2()
    {
        $logger = $this->getMock(
            'Plop_Logger_Stub',
            array('filter', '_callHandlers')
        );

        $logger
            ->expects($this->once())
            ->method('filter')
            ->with($this->_record)
            ->will($this->returnValue(FALSE));
        $logger
            ->expects($this->never())
            ->method('_callHandlers');

        $this->assertSame($logger, $logger->handleStub($this->_record));
    }

    /**
     * @covers Plop_Logger::log
     */
    public function testLogMethod()
    {
        $logger = $this->getMock(
            'Plop_Logger_Stub',
            array('_handle')
        );

        $this->_factory
            ->expects($this->once())
            ->method('createRecord')
            ->will($this->returnValue($this->_record));
        $logger
            ->expects($this->once())
            ->method('_handle')
            ->with($this->_record);

        $logger->setLevel(Plop::DEBUG);
        $logger->setRecordFactory($this->_factory);
        $this->assertSame($logger, $logger->log(Plop::ERROR, 'foo'));
    }

    /**
     * @covers Plop_Logger::log
     */
    public function testLogMethod2()
    {
        $logger = $this->getMock(
            'Plop_Logger_Stub',
            array('_handle')
        );

        $this->_factory
            ->expects($this->never())
            ->method('createRecord');
        $logger
            ->expects($this->never())
            ->method('_handle');

        $logger->setLevel(Plop::ERROR);
        $logger->setRecordFactory($this->_factory);
        $this->assertSame($logger, $logger->log(Plop::DEBUG, 'foo'));
    }
}
