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

namespace Plop\Interpolator;

/**
 * \brief
 *      An interpolator that uses a syntax similar
 *      to Python's old formatting syntax: %(value)s.
 */
class Percent implements \Plop\InterpolatorInterface
{
    /// \copydoc Plop::InterpolatorInterface::interpolate().
    public function interpolate($msg, array $args = array())
    {
        preg_match_all('/(?<!%)(?:%%)*%\\(([^\\)]*)\\)/', $msg, $matches);
        // Only define the variables if there are any.
        if (isset($matches[1][0])) {
            $args += array_combine(
                $matches[1],
                array_fill(0, count($matches[1]), null)
            );
        }

        if (!count($args)) {
            return $msg;
        }

        // Mapping = array(name => index)
        $keys       = array_keys($args);
        $mapping    = array_flip($keys);
        $keys       = array_map(function ($key) { return "%($key)"; }, $keys);
        $values     = array_map(function ($val) { return '%'.($val + 1).'$'; }, $mapping);
        $mapping    = array_combine($keys, $values);
        $msg        = strtr($msg, $mapping);
        return vsprintf($msg, array_values($args));
    }
}
