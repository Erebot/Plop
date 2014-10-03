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

namespace Plop\Tests;

class RecordFactory extends \Plop_TestCase
{
    /**
     * @covers \Plop\RecordFactory::createRecord
     */
    public function testRecordProduction()
    {
        $factory    = new \Plop\RecordFactory();
        $record     = $factory->createRecord(
            'foo',
            'bar',
            'baz',
            __NAMESPACE__,
            substr(__CLASS__, strrpos('\\' . __CLASS__, '\\')),
            substr(__FUNCTION__, strrpos('\\' . __FUNCTION__, '\\')),
            \Plop\INFO,
            __FILE__,
            __LINE__,
            'test',
            array(),
            null
        );
        $this->assertTrue($record instanceof \Plop\RecordInterface);
    }
}
