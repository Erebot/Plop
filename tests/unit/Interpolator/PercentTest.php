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

namespace Plop\Tests\Interpolator;

class Percent extends \Plop_TestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->interpolator = new \Plop\Interpolator\Percent();
    }

    public function testdata()
    {
        return array(
            array('foo', 'foo', array()),
            array('bar', '%(foo)s', array('foo' => 'bar')),
            array('007', '%(Bond)03d', array('Bond' => 7)),
            array('3.14', '%(PI).2f', array('PI' => 3.14159265)),
            array('%', '%(foo)s', array('foo' => '%')),
            array('foo', '%(%)s', array('%' => 'foo')),
        );
    }

    /**
     * @dataProvider    testdata
     * @covers          \Plop\Interpolator\Percent::interpolate
     */
    public function testInterpolation($expected, $pattern, $args)
    {
        $this->assertSame(
            $expected,
            $this->interpolator->interpolate($pattern, $args)
        );
    }
}
