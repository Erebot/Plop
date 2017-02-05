<?php
/*
    This file is part of Plop, a simple logging library for PHP.

    Copyright © 2010-2014 François Poirotte

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

namespace Plop\Filter;

/**
 *  \brief
 *      A filter that inverts the result of another filter.
 */
class Not implements \Plop\FilterInterface
{
    /// Subfilter to negate.
    protected $subfilter;

    /**
     * Construct a new instance of this filter.
     *
     * \param Plop::FilterInterface $filter
     *      Subfilter whose filtering effect will be
     *      inverted by this filter instance.
     */
    public function __construct(\Plop\FilterInterface $filter)
    {
        $this->subfilter = $filter;
    }

    /// \copydoc Plop::FilterInterface::filter
    public function filter(\Plop\RecordInterface $record)
    {
        $res = !$this->subfilter->filter($record);
        return $res;
    }
}
