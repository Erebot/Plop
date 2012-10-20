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

class   Plop_Logger_Test
extends Plop_TestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->_factory = $this->getMock('Plop_RecordFactoryInterface');
        $this->_record  = $this->getMock('Plop_RecordInterface');
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
        $logger = new Plop_Logger();
        $this->assertSame(NULL, $logger->getFile());
        $this->assertSame(NULL, $logger->getClass());
        $this->assertSame(NULL, $logger->getMethod());
    }

    /**
     * @covers Plop_Logger::__construct
     * @covers Plop_Logger::getFile
     * @covers Plop_Logger::getClass
     * @covers Plop_Logger::getMethod
     */
    public function testConstructorWithSpecificArguments()
    {
        $logger = new Plop_Logger(__FILE__, __CLASS__, __METHOD__);
        $this->assertSame(__FILE__, $logger->getFile());
        $this->assertSame(__CLASS__, $logger->getClass());
        $this->assertSame(__METHOD__, $logger->getMethod());
    }
}
