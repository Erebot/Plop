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

namespace Plop;

interface InterpolatorInterface
{
    /**
     * Interpolate percent-sequence-variables in
     * a message.
     *
     * \param string $msg
     *      The message that serves as the model for the result.
     *      It may refer to the values of variables dynamically
     *      using special formatting sequences.
     *
     * \param array $args
     *      (optional) Associative array of values that will
     *      be dynamically replaced in the message.
     *      Defaults to an empty array.
     *
     * \retval string
     *      The message with all of its formatting-sequences
     *      replaced with their proper value.
     */
    public function interpolate($msg, array $args = array());
}
