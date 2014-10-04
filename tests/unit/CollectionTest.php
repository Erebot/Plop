<?php
/*
    This file is part of Plop, a simple logging library for PHP.

    Copyright © 2014 François Poirotte

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

class Collection extends \Plop_TestCase
{
    public function setUp()
    {
        parent::setUp();
    }

    /**
     * @covers \Plop\Collection::count
     * @covers \Plop\Collection::offsetGet
     * @covers \Plop\Collection::offsetSet
     * @covers \Plop\Collection::offsetExists
     * @covers \Plop\Collection::offsetUnset
     */
    public function testNullTypeHint()
    {
        $object = new \stdClass();
        $collection = new \Plop\Collection();

        // The collection is empty when created.
        $this->assertSame(0, count($collection));

        // Objects can be stored and their presence tested
        // using either the object itself or its offset.
        $collection[] = $object;
        $this->assertSame(1, count($collection));
        $this->assertSame($object, $collection[0]);
        $this->assertTrue(isset($collection[$object]));
        $this->assertTrue(isset($collection[0]));

        // Objects can be added multiple times.
        $collection[] = $object;
        $this->assertSame(2, count($collection));

        // Objects can be unset using the object itself.
        $collection = new \Plop\Collection();
        $collection[42] = $object;
        unset($collection[$object]);
        $this->assertFalse(isset($collection[42]));

        // Objects can be unset using an offset.
        $collection = new \Plop\Collection();
        $collection[] = $object;
        unset($collection[0]);
        $this->assertFalse(isset($collection[$object]));
    }

    /**
     * @covers                      \Plop\Collection::offsetSet
     */
    public function testTypeHintWithValidObject()
    {
        $collection = new \Plop\Stub\CollectionHelper();
        $collection2 = new \Plop\Stub\CollectionHelper();
        $collection[] = $collection2;
        $this->assertSame(1, count($collection));
    }

    /**
     * @covers                      \Plop\Collection::offsetSet
     * @expectedException           \Plop\Exception
     * @expectedExceptionMessage    An instance of \Plop\Stub\CollectionHelper was expected
     */
    public function testTypeHintWithInvalidObject()
    {
        $object = new \stdClass();
        $collection = new \Plop\Stub\CollectionHelper();
        $collection[] = $object;
    }
}
