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
    DIRECTORY_SEPARATOR . 'RecordInterface.php'
);

class   Plop_IndirectLoggerAbstract_Test
extends Plop_TestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->_sublogger   = $this->getMock(
            'Plop_LoggerAbstract',
            array(),
            array(),
            '',
            FALSE
        );

        $this->_indirect    = $this->getMock(
            'Plop_IndirectLoggerAbstract',
            array('_getIndirectLogger'),
            array(),
            '',
            FALSE
        );

        $this->_indirect
            ->expects($this->once())
            ->method('_getIndirectLogger')
            ->will($this->returnValue($this->_sublogger));
    }

    public function providerActions()
    {
        $handler    = $this->getMock('Plop_HandlerInterface');
        $filter     = $this->getMock('Plop_FilterInterface');
        $record     = $this->getMock('Plop_RecordInterface_Stub');
        $factory    = $this->getMock('Plop_RecordFactoryInterface');

        return array(
            array('log', array(Plop::DEBUG, 'foo'), TRUE, NULL),

            array('getLevel', array(), FALSE, Plop::DEBUG),
            array('setLevel', array(Plop::DEBUG), TRUE, NULL),
            array('isEnabledFor', array(Plop::DEBUG), FALSE, TRUE),
            array('isEnabledFor', array(Plop::DEBUG), FALSE, FALSE),

            array('getFile', array(), FALSE, __FILE__),
            array('getClass', array(), FALSE, __CLASS__),
            array('getMethod', array(), FALSE, __METHOD__),

            array('getRecordFactory', array(), FALSE, $factory),
            array('setRecordFactory', array($factory), TRUE, NULL),

            array('addHandler', array($handler), TRUE, NULL),
            array('removeHandler', array($handler), TRUE, NULL),
            array('getHandlers', array(), FALSE, array($handler)),

            array('addFilter', array($filter), TRUE, NULL),
            array('removeFilter', array($filter), TRUE, NULL),
            array('getFilters', array(), FALSE, array($filter)),
            array('filter', array($record), FALSE, TRUE),
            array('filter', array($record), FALSE, FALSE),
        );
    }

    /**
     * @dataProvider providerActions
     * @covers Plop_IndirectLoggerAbstract::log
     * @covers Plop_IndirectLoggerAbstract::getLevel
     * @covers Plop_IndirectLoggerAbstract::setLevel
     * @covers Plop_IndirectLoggerAbstract::isEnabledFor
     * @covers Plop_IndirectLoggerAbstract::getFile
     * @covers Plop_IndirectLoggerAbstract::getClass
     * @covers Plop_IndirectLoggerAbstract::getMethod
     * @covers Plop_IndirectLoggerAbstract::getRecordFactory
     * @covers Plop_IndirectLoggerAbstract::setRecordFactory
     * @covers Plop_IndirectLoggerAbstract::addHandler
     * @covers Plop_IndirectLoggerAbstract::removeHandler
     * @covers Plop_IndirectLoggerAbstract::getHandlers
     * @covers Plop_IndirectLoggerAbstract::addFilter
     * @covers Plop_IndirectLoggerAbstract::removeFilter
     * @covers Plop_IndirectLoggerAbstract::getFilters
     * @covers Plop_IndirectLoggerAbstract::filter
     */
    public function testActions($method, $args, $chainable, $output)
    {
        $expectation = $this->_sublogger
            ->expects($this->once())
            ->method($method);
        call_user_func_array(array($expectation, 'with'), $args);

        if ($chainable) {
            $expectation->will($this->returnValue($this->_sublogger));
            $this->assertSame(
                $this->_indirect,
                call_user_func_array(array($this->_indirect, $method), $args)
            );
        }
        else {
            $expectation->will($this->returnValue($output));
            $this->assertSame(
                $output,
                call_user_func_array(array($this->_indirect, $method), $args)
            );
        }
    }
}

