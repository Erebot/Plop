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

class IndirectLoggerAbstract extends \Plop_TestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->sublogger   = $this->getMock(
            '\\Plop\\LoggerAbstract',
            array(),
            array(),
            '',
            false
        );

        $this->indirect    = $this->getMock(
            '\\Plop\\IndirectLoggerAbstract',
            array('getIndirectLogger'),
            array(),
            '',
            false
        );

        $this->indirect
            ->expects($this->once())
            ->method('getIndirectLogger')
            ->will($this->returnValue($this->sublogger));
    }

    public function providerActions()
    {
        $handler    = $this->getMock('\\Plop\\HandlerInterface');
        $record     = $this->getMock('\\Plop\\RecordInterface_Stub');
        $factory    = $this->getMock('\\Plop\\RecordFactoryInterface');

        return array(
            array('log', array(\Plop\DEBUG, 'foo'), true, null),

            array('getLevel', array(), false, \Plop\DEBUG),
            array('setLevel', array(\Plop\DEBUG), true, null),
            array('isEnabledFor', array(\Plop\DEBUG), false, true),
            array('isEnabledFor', array(\Plop\DEBUG), false, false),

            array('getNamespace', array(), false, __NAMESPACE__),
            array('getClass', array(), false, __CLASS__),
            array('getMethod', array(), false, __FUNCTION__),

            array('getRecordFactory', array(), false, $factory),
            array('setRecordFactory', array($factory), true, null),
        );
    }

    /**
     * @dataProvider providerActions
     * @covers \Plop\IndirectLoggerAbstract::log
     * @covers \Plop\IndirectLoggerAbstract::getLevel
     * @covers \Plop\IndirectLoggerAbstract::setLevel
     * @covers \Plop\IndirectLoggerAbstract::isEnabledFor
     * @covers \Plop\IndirectLoggerAbstract::getNamespace
     * @covers \Plop\IndirectLoggerAbstract::getClass
     * @covers \Plop\IndirectLoggerAbstract::getMethod
     * @covers \Plop\IndirectLoggerAbstract::getRecordFactory
     * @covers \Plop\IndirectLoggerAbstract::setRecordFactory
     */
    public function testActions($method, $args, $chainable, $output)
    {
        $expectation = $this->sublogger
            ->expects($this->once())
            ->method($method);
        call_user_func_array(array($expectation, 'with'), $args);

        if ($chainable) {
            $expectation->will($this->returnValue($this->sublogger));
            $this->assertSame(
                $this->indirect,
                call_user_func_array(array($this->indirect, $method), $args)
            );
        } else {
            $expectation->will($this->returnValue($output));
            $this->assertSame(
                $output,
                call_user_func_array(array($this->indirect, $method), $args)
            );
        }
    }

    /**
     * @covers \Plop\IndirectLoggerAbstract::getFilters
     */
    public function testGetFilters()
    {
        $collection = new \Plop\FiltersCollection();
        $this->sublogger
            ->expects($this->once())
            ->method('getFilters')
            ->will($this->returnValue($collection));
        $this->assertSame($collection, $this->indirect->getFilters());
    }

    /**
     * @covers \Plop\IndirectLoggerAbstract::setFilters
     */
    public function testSetFilters()
    {
        $collection = new \Plop\FiltersCollection();
        $this->sublogger
            ->expects($this->once())
            ->method('setFilters')
            ->will($this->returnValue($this->sublogger));
        // setFilters() returns $this.
        $this->assertSame($this->indirect, $this->indirect->setFilters($collection));
    }

    /**
     * @covers \Plop\IndirectLoggerAbstract::getHandlers
     */
    public function testGetHandlers()
    {
        $collection = new \Plop\HandlersCollection();
        $this->sublogger
            ->expects($this->once())
            ->method('getHandlers')
            ->will($this->returnValue($collection));
        $this->assertSame($collection, $this->indirect->getHandlers());
    }

    /**
     * @covers \Plop\IndirectLoggerAbstract::setHandlers
     */
    public function testSetHandlers()
    {
        $collection = new \Plop\HandlersCollection();
        $this->sublogger
            ->expects($this->once())
            ->method('setHandlers')
            ->will($this->returnValue($this->sublogger));
        // setHandlers() returns $this.
        $this->assertSame($this->indirect, $this->indirect->setHandlers($collection));
    }
}
