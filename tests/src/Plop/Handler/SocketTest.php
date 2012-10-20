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

class   Plop_Handler_Socket_Test
extends Plop_TestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->_line    = __LINE__;
        $this->_record  = new Plop_Record(
            'name',
            Plop::DEBUG,
            __FILE__,
            $this->_line,
            '@ %(foo)s @',
            array('foo' => 'bar')
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
        $this->assertTrue(isset($this->_record['name']));
        $this->assertSame('name', $this->_record['name']);
        $this->_record['name'] = 'new name';
        $this->assertSame('new name', $this->_record['name']);
        unset($this->_record['name']);
        $this->assertFalse(isset($this->_record['name']));
    }
}
