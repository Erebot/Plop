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
        $this->_plop = new Plop_Stub();
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
}
