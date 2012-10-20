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
    DIRECTORY_SEPARATOR . 'Filterer.php'
);

class   FiltererTest
extends Plop_TestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->_filterer    = new Plop_FiltererStub();
        $this->_filter      = $this->getMock('Plop_FilterInterface');
        $this->_record      = $this->getMock('Plop_RecordInterface');
    }

    /**
     * @covers Plop_Filterer::__construct
     * @covers Plop_Filterer::addFilter
     * @covers Plop_Filterer::removeFilter
     * @covers Plop_Filterer::getFilters
     */
    public function testAdditionsAndRemovals()
    {
        // No filters by default.
        $this->assertSame(0, count($this->_filterer->getFilters()));

        // Additions must work.
        $this->assertSame(
            $this->_filterer,
            $this->_filterer->addFilter($this->_filter)
        );
        $this->assertSame(
            array($this->_filter),
            $this->_filterer->getFilters()
        );

        // The same filter cannot be registered twice.
        $this->assertSame(
            $this->_filterer,
            $this->_filterer->addFilter($this->_filter)
        );
        $this->assertSame(
            array($this->_filter),
            $this->_filterer->getFilters()
        );

        // Removals must also work.
        $this->assertSame(
            $this->_filterer,
            $this->_filterer->removeFilter($this->_filter)
        );
        $this->assertSame(array(), $this->_filterer->getFilters());

        // Trying to remove the same filter twice does nothing,
        // and does not generate any exception.
        $this->assertSame(
            $this->_filterer,
            $this->_filterer->removeFilter($this->_filter)
        );
        $this->assertSame(array(), $this->_filterer->getFilters());
    }

    /**
     * @covers Plop_Filterer::filter
     */
    public function testFiltering()
    {
        // An empty filterer always accepts records.
        $this->assertTrue($this->_filterer->filter($this->_record));
    }

    /**
     * @covers Plop_Filterer::filter
     */
    public function testFiltering2()
    {
        $this->_filterer->addFilter($this->_filter);
        $this->_filter
            ->expects($this->once())
            ->method('filter')
            ->with($this->equalTo($this->_record))
            ->will($this->returnValue(FALSE));

        // If one filter rejects the record,
        // then the whole filterer does too.
        $this->assertFalse($this->_filterer->filter($this->_record));
    }

    /**
     * @covers Plop_Filterer::filter
     */
    public function testFiltering3()
    {
        $this->_filterer->addFilter($this->_filter);
        $this->_filter
            ->expects($this->once())
            ->method('filter')
            ->with($this->equalTo($this->_record))
            ->will($this->returnValue(TRUE));

        // If the record passed all filters,
        // then it passes the filterer too.
        $this->assertTrue($this->_filterer->filter($this->_record));
    }
}
