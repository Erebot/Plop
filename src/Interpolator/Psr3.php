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
 *      An interpolator that complies with
 *      the PSR-3 specification.
 *
 * \see
 *      http://www.php-fig.org/psr/psr-3/
 */
class Psr3 implements \Plop\InterpolatorInterface
{
    /**
     * \copydoc Plop::InterpolatorInterface::interpolate()
     *
     * \note
     *      The following piece of code was almost taken as is
     *      from the PSR-3 documentation with the addition of
     *      a visibility keyword.
     *
     * \copyright
     *      Copyright © Jordi Boggiano and other PSR-3 contributors.
     */
    public function interpolate($message, array $args = array())
    {
        $replace = array();
        foreach ($args as $key => $val) {
            $replace['{' . $key . '}'] = $val;
        }
        return strtr($message, $replace);
    }
}
